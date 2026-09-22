const STORAGE_KEY = 'pomodoro-timer-state-v2';

const PHASE_LABELS = {
    focus: 'Estudio',
    short_break: 'Descanso corto',
    long_break: 'Descanso largo',
};

let audioCtx = null;

function initPomodoroTimer() {
    const root = document.getElementById('pomodoro-timer');

    if (!root || root.dataset.initialized === '1') {
        return;
    }

    root.dataset.initialized = '1';

    const config = JSON.parse(root.dataset.config || '{}');

    const durations = {
        focus: (config.study || 25) * 60 * 1000,
        short_break: (config.shortBreak || 5) * 60 * 1000,
        long_break: (config.longBreak || 15) * 60 * 1000,
    };

    const el = {
        phase: root.querySelector('[data-role="phase"]'),
        time: root.querySelector('[data-role="time"]'),
        progress: root.querySelector('[data-role="progress"]'),
        cycles: root.querySelector('[data-role="cycles"]'),
        toggle: root.querySelector('[data-role="toggle"]'),
        reset: root.querySelector('[data-role="reset"]'),
        skip: root.querySelector('[data-role="skip"]'),
        todayCount: root.querySelector('[data-role="today-count"]'),
        todayMinutes: root.querySelector('[data-role="today-minutes"]'),
    };

    const state = {
        phase: 'focus',
        running: false,
        endTime: 0,
        remainingMs: durations.focus,
        durationMs: durations.focus,
        cycleCount: 0,
    };

    let ticker = null;
    let lastPersist = 0;

    restore();

    el.toggle.addEventListener('click', toggle);
    el.reset.addEventListener('click', () => reset(true));
    el.skip.addEventListener('click', () => completePhase(true));

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden && state.running) {
            if (state.endTime - Date.now() <= 0) {
                completePhase();
            } else {
                render();
            }
        }
    });

    render();
    if (state.running) {
        startTicker();
        persist(true);
    }

    function remainingMs() {
        return state.running
            ? Math.max(0, state.endTime - Date.now())
            : state.remainingMs;
    }

    function startTicker() {
        clearInterval(ticker);
        ticker = setInterval(tick, 250);
    }

    function tick() {
        if (!state.running) {
            return;
        }

        if (state.endTime - Date.now() <= 0) {
            completePhase();

            return;
        }

        render();

        if (Date.now() - lastPersist > 1000) {
            persist();
        }
    }

    function start() {
        if (state.running) {
            return;
        }

        ensureNotificationPermission();
        resumeAudio();

        state.running = true;
        state.endTime = Date.now() + state.remainingMs;
        startTicker();
        render();
        persist(true);
    }

    function pause() {
        if (!state.running) {
            return;
        }

        state.remainingMs = remainingMs();
        state.running = false;
        clearInterval(ticker);
        render();
        persist(true);
    }

    function toggle() {
        state.running ? pause() : start();
    }

    function reset(notifyUser) {
        clearInterval(ticker);
        state.running = false;
        state.durationMs = durations[state.phase];
        state.remainingMs = state.durationMs;
        state.endTime = 0;
        render();
        persist(true);

        if (notifyUser) {
            notify(PHASE_LABELS[state.phase] + ' reiniciado', 'El bloque volvió a empezar.');
        }
    }

    function setPhase(phase, autoStart) {
        state.phase = phase;
        state.durationMs = durations[phase];
        state.remainingMs = state.durationMs;
        state.running = false;
        state.endTime = 0;
        clearInterval(ticker);
        render();
        persist(true);

        if (autoStart) {
            start();
        }
    }

    function completePhase(skipped = false) {
        const finished = state.phase;

        clearInterval(ticker);
        state.running = false;

        if (!skipped) {
            ding();
            notify(PHASE_LABELS[finished] + ' finalizado', messageForFinished(finished));
        }

        let next;

        if (finished === 'focus') {
            if (!skipped) {
                state.cycleCount += 1;
                logPomodoro(config.study || 25);
            }

            next = state.cycleCount > 0 && state.cycleCount % (config.cycles || 4) === 0
                ? 'long_break'
                : 'short_break';

            setPhase(next, config.autoStartBreaks);
        } else {
            if (finished === 'long_break') {
                state.cycleCount = 0;
            }

            next = 'focus';
            setPhase(next, config.autoStartPomodoros);
        }

        notify(PHASE_LABELS[next] + ' iniciado', '¡A por ello!');
    }

    function messageForFinished(phase) {
        if (phase === 'focus') {
            return 'Buen trabajo, es momento de descansar.';
        }

        return 'El descanso terminó, volvamos a concentrarnos.';
    }

    function render() {
        const rem = remainingMs();
        const duration = state.durationMs || durations[state.phase];

        if (el.phase) {
            el.phase.textContent = PHASE_LABELS[state.phase];
        }

        if (el.time) {
            el.time.textContent = formatTime(rem);
        }

        if (el.progress) {
            const circumference = 2 * Math.PI * 110;
            const elapsed = Math.min(1, Math.max(0, 1 - rem / duration));
            el.progress.style.strokeDasharray = circumference;
            el.progress.style.strokeDashoffset = circumference * (1 - elapsed);
        }

        if (el.toggle) {
            el.toggle.textContent = state.running ? 'Pausar' : 'Iniciar';
        }

        renderCycles();

        document.title = state.running
            ? formatTime(rem) + ' · ' + PHASE_LABELS[state.phase]
            : 'Pomodoro';
    }

    function renderCycles() {
        if (!el.cycles) {
            return;
        }

        const total = config.cycles || 4;
        let filled = state.cycleCount % total;

        if (state.phase === 'long_break' && state.cycleCount > 0 && state.cycleCount % total === 0) {
            filled = total;
        }

        el.cycles.innerHTML = '';

        for (let i = 0; i < total; i++) {
            const dot = document.createElement('span');
            dot.className = 'h-2.5 w-2.5 rounded-full transition ' + (
                i < filled ? 'bg-red-500' : 'bg-gray-300'
            );
            el.cycles.appendChild(dot);
        }
    }

    function formatTime(ms) {
        const totalSeconds = Math.ceil(ms / 1000);
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;

        return String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
    }

    function persist(force = false) {
        lastPersist = Date.now();

        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify({
                phase: state.phase,
                running: state.running,
                endTime: state.endTime,
                remainingMs: remainingMs(),
                durationMs: state.durationMs,
                cycleCount: state.cycleCount,
                savedAt: Date.now(),
            }));
        } catch (e) {
            // storage unavailable
        }
    }

    function restore() {
        let saved;

        try {
            saved = JSON.parse(localStorage.getItem(STORAGE_KEY) || 'null');
        } catch (e) {
            saved = null;
        }

        if (!saved || !durations[saved.phase]) {
            return;
        }

        state.phase = saved.phase;
        state.durationMs = saved.durationMs || durations[saved.phase];
        state.cycleCount = saved.cycleCount || 0;

        if (saved.running && saved.endTime) {
            const rem = saved.endTime - Date.now();

            if (rem > 0) {
                state.running = true;
                state.endTime = saved.endTime;
                state.remainingMs = rem;
            } else {
                state.running = false;
                state.remainingMs = 0;
            }
        } else {
            state.running = false;
            state.remainingMs = typeof saved.remainingMs === 'number'
                ? saved.remainingMs
                : durations[saved.phase];
        }
    }

    async function logPomodoro(durationMinutes) {
        const token = document.querySelector('meta[name="csrf-token"]');

        try {
            const response = await fetch('/api/pomodoros', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token ? token.content : '',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    phase: 'focus',
                    duration_minutes: durationMinutes,
                    completed_at: new Date().toISOString(),
                    status: 'completed',
                }),
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();

            if (el.todayCount) {
                el.todayCount.textContent = data.today_count;
            }

            if (el.todayMinutes) {
                el.todayMinutes.textContent = data.today_minutes;
            }
        } catch (e) {
            // offline: the pomodoro will not be persisted
        }
    }

    function ensureNotificationPermission() {
        if (!config.notifications || !('Notification' in window)) {
            return;
        }

        if (Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }

    function notify(title, body) {
        if (!config.notifications || !('Notification' in window)) {
            return;
        }

        if (Notification.permission !== 'granted') {
            return;
        }

        try {
            new Notification(title, {
                body: body,
                icon: '/icons/icon-192.png',
                badge: '/icons/icon-192.png',
                tag: 'pomodoro-phase',
                renotify: true,
            });
        } catch (e) {
            // notification failed
        }
    }

    function resumeAudio() {
        if (audioCtx && audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
    }

    function ding() {
        if (!config.sound) {
            return;
        }

        try {
            const Ctx = window.AudioContext || window.webkitAudioContext;

            if (!Ctx) {
                return;
            }

            audioCtx = audioCtx || new Ctx();

            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }

            const now = audioCtx.currentTime;

            [880, 1174.66].forEach((frequency, index) => {
                const oscillator = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                const start = now + index * 0.18;

                oscillator.type = 'sine';
                oscillator.frequency.value = frequency;

                gain.gain.setValueAtTime(0.0001, start);
                gain.gain.linearRampToValueAtTime(0.35, start + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.0001, start + 0.4);

                oscillator.connect(gain);
                gain.connect(audioCtx.destination);
                oscillator.start(start);
                oscillator.stop(start + 0.45);
            });
        } catch (e) {
            // audio unavailable
        }
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPomodoroTimer);
} else {
    initPomodoroTimer();
}
