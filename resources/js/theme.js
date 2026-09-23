import Alpine from 'alpinejs';

const THEME_KEY = 'pomodoro-theme-v1';
const MODES = ['light', 'dark', 'system'];
const CYCLE_ORDER = ['system', 'light', 'dark'];

function sanitizeMode(value) {
    return MODES.includes(value) ? value : 'system';
}

function getStoredMode() {
    try {
        return sanitizeMode(localStorage.getItem(THEME_KEY));
    } catch (e) {
        return 'system';
    }
}

function prefersDarkOS() {
    return window.matchMedia
        ? window.matchMedia('(prefers-color-scheme: dark)').matches
        : false;
}

function resolveEffective(mode) {
    const current = mode ?? getStoredMode();

    if (current === 'system') {
        return prefersDarkOS() ? 'dark' : 'light';
    }

    return current;
}

function applyTheme(mode) {
    const effective = resolveEffective(mode);

    document.documentElement.classList.toggle('dark', effective === 'dark');
    window.dispatchEvent(
        new CustomEvent('theme-change', { detail: { effective, mode: mode ?? getStoredMode() } })
    );

    return effective;
}

function registerThemeStore() {
    Alpine.store('theme', {
        mode: getStoredMode(),
        effective: resolveEffective(),

        set(mode) {
            this.mode = sanitizeMode(mode);

            try {
                localStorage.setItem(THEME_KEY, this.mode);
            } catch (e) {
                // storage unavailable: keep in-memory mode only
            }

            this.effective = applyTheme(this.mode);
        },

        cycle() {
            const next = CYCLE_ORDER[(CYCLE_ORDER.indexOf(this.mode) + 1) % CYCLE_ORDER.length];
            this.set(next);
        },
    });
}

applyTheme(getStoredMode());

document.addEventListener('alpine:init', registerThemeStore);

if (window.matchMedia) {
    const media = window.matchMedia('(prefers-color-scheme: dark)');

    const handleOSChange = () => {
        const store = Alpine.store('theme');

        if (store && store.mode === 'system') {
            store.effective = applyTheme('system');
        } else if (!store) {
            applyTheme('system');
        }
    };

    if (typeof media.addEventListener === 'function') {
        media.addEventListener('change', handleOSChange);
    } else if (typeof media.addListener === 'function') {
        media.addListener(handleOSChange);
    }
}
