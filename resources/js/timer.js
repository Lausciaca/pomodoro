const STORAGE_KEY = 'pomodoro-timer-state-v2';

const PHASE_LABELS = {
    focus: 'Estudio',
    short_break: 'Descanso corto',
    long_break: 'Descanso largo',
};

const PHASE_THEME = {
    focus: { color: '#dc2626', soft: '#fee2e2' },
    short_break: { color: '#059669', soft: '#d1fae5' },
    long_break: { color: '#2563eb', soft: '#dbeafe' },
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
        hint: root.querySelector('[data-role="hint"]'),
        progress: root.querySelector('[data-role="progress"]'),
        cycles: root.querySelector('[data-role="cycles"]'),
        toggle: root.querySelector('[data-role="toggle"]'),
        reset: root.querySelector('[data-role="reset"]'),
        skip: root.querySelector('[data-role="skip"]'),
        todayCount: root.querySelector('[data-role="today-count"]'),
        todayMinutes: root.querySelector('[data-role="today-minutes"]'),
        goalCount: root.querySelector('[data-role="goal-count"]'),
        goalBar: root.querySelector('[data-role="goal-bar"]'),
        goalMessage: root.querySelector('[data-role="goal-message"]'),
        banner: document.querySelector('[data-role="notification-banner"]'),
        enableNotifications: document.querySelector('[data-role="enable-notifications"]'),
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

    if (el.enableNotifications) {
        el.enableNotifications.addEventListener('click', requestNotifications);
    }

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden && state.running) {
            if (state.endTime - Date.now() <= 0) {
                completePhase();
            } else {
                render();
            }
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.code !== 'Space' && event.key !== ' ') {
            return;
        }

        const target = event.target;

        if (target && (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.isContentEditable)) {
            return;
        }

        event.preventDefault();
        toggle();
    });

    refreshNotificationBanner();
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
        } else {
            if (finished === 'long_break') {
                state.cycleCount = 0;
            }

            next = 'focus';
        }

        if (!skipped) {
            notify(
                PHASE_LABELS[finished] + ' finalizado',
                messageForFinished(finished) + ' Siguiente: ' + PHASE_LABELS[next].toLowerCase() + '.'
            );
        }

        setPhase(next, finished === 'focus' ? config.autoStartBreaks : config.autoStartPomodoros);
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

        if (el.hint) {
            el.hint.textContent = state.running ? 'Enfocate en una sola cosa' : 'Presioná Espacio';
        }

        applyTheme();
        renderCycles();
        renderGoal();

        document.title = state.running
            ? formatTime(rem) + ' · ' + PHASE_LABELS[state.phase]
            : 'Pomodoro';
    }

    function applyTheme() {
        const theme = PHASE_THEME[state.phase] || PHASE_THEME.focus;

        if (el.phase) {
            el.phase.style.color = theme.color;
        }

        if (el.progress) {
            el.progress.style.stroke = theme.color;
        }

        if (el.toggle) {
            el.toggle.style.backgroundColor = theme.color;
        }

        if (el.goalBar) {
            el.goalBar.style.backgroundColor = theme.color;
        }
    }

    function renderCycles() {
        if (!el.cycles) {
            return;
        }

        const total = config.cycles || 4;
        const theme = PHASE_THEME[state.phase] || PHASE_THEME.focus;
        let filled = state.cycleCount % total;

        if (state.phase === 'long_break' && state.cycleCount > 0 && state.cycleCount % total === 0) {
            filled = total;
        }

        el.cycles.innerHTML = '';

        for (let i = 0; i < total; i++) {
            const dot = document.createElement('span');
            dot.className = 'h-2.5 w-2.5 rounded-full transition';
            dot.style.backgroundColor = i < filled ? theme.color : '#cbd5e1';
            el.cycles.appendChild(dot);
        }
    }

    function currentCount() {
        return el.todayCount ? parseInt(el.todayCount.textContent, 10) || 0 : 0;
    }

    function renderGoal() {
        const goal = Math.max(1, config.dailyGoal || 1);
        const count = currentCount();
        const percent = Math.min(100, Math.round((count / goal) * 100));

        if (el.goalCount) {
            el.goalCount.textContent = goal;
        }

        if (el.goalBar) {
            el.goalBar.style.width = percent + '%';
        }

        if (el.goalMessage) {
            if (count >= goal) {
                el.goalMessage.textContent = '¡Meta diaria cumplida!';
                el.goalMessage.className = 'mt-2 text-xs font-medium text-emerald-600';
            } else {
                el.goalMessage.textContent = 'Te faltan ' + (goal - count) + ' pomodoros para tu meta de hoy.';
                el.goalMessage.className = 'mt-2 text-xs text-slate-500';
            }
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

            if (typeof data.daily_goal === 'number') {
                config.dailyGoal = data.daily_goal;
            }

            if (el.todayCount) {
                el.todayCount.textContent = data.today_count;
            }

            if (el.todayMinutes) {
                el.todayMinutes.textContent = data.today_minutes;
            }

            renderGoal();

            if (data.goal_reached && data.today_count === config.dailyGoal) {
                notify('¡Meta diaria cumplida!', 'Completaste ' + config.dailyGoal + ' pomodoros hoy.');
            }
        } catch (e) {
            // offline: the pomodoro will not be persisted
        }
    }

    function supportsNotifications() {
        return 'Notification' in window;
    }

    function notificationState() {
        if (!supportsNotifications()) {
            return 'unsupported';
        }

        return Notification.permission;
    }

    function refreshNotificationBanner() {
        if (!el.banner) {
            return;
        }

        if (!config.notifications || !supportsNotifications()) {
            el.banner.classList.add('hidden');
            el.banner.classList.remove('flex');

            return;
        }

        const permission = notificationState();

        if (permission === 'granted') {
            el.banner.classList.add('hidden');
            el.banner.classList.remove('flex');

            return;
        }

        el.banner.classList.remove('hidden');
        el.banner.classList.add('flex');

        if (permission === 'denied') {
            el.banner.firstElementChild.textContent = 'Las notificaciones están bloqueadas. Habilitalas desde la configuración del navegador.';

            if (el.enableNotifications) {
                el.enableNotifications.classList.add('hidden');
            }
        }
    }

    function ensureNotificationPermission() {
        if (!config.notifications || !supportsNotifications()) {
            return;
        }

        if (Notification.permission === 'default') {
            Notification.requestPermission().then(refreshNotificationBanner);
        }
    }

    async function requestNotifications() {
        if (!supportsNotifications()) {
            return;
        }

        try {
            const permission = await Notification.requestPermission();
            refreshNotificationBanner();

            if (permission === 'granted') {
                showNotification('Notificaciones activadas', 'Avisaremos cuando termine cada bloque.');
            }
        } catch (e) {
            // ignore
        }
    }

    async function getServiceWorkerRegistration() {
        if (!('serviceWorker' in navigator)) {
            return null;
        }

        try {
            return await navigator.serviceWorker.ready;
        } catch (e) {
            return null;
        }
    }

    async function showNotification(title, body) {
        if (!config.notifications || !supportsNotifications() || Notification.permission !== 'granted') {
            return;
        }

        const options = {
            body: body,
            icon: '/icons/icon-192.png',
            badge: '/icons/icon-192.png',
            tag: 'pomodoro-phase',
            renotify: true,
        };

        const registration = await getServiceWorkerRegistration();

        if (registration && typeof registration.showNotification === 'function') {
            try {
                await registration.showNotification(title, options);

                return;
            } catch (e) {
                // fall back to the constructor below
            }
        }

        try {
            new Notification(title, options);
        } catch (e) {
            // notification failed
        }
    }

    function notify(title, body) {
        showNotification(title, body);
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
