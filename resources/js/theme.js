const STORAGE_KEY = 'theme';

function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function isDark() {
    return document.documentElement.classList.contains('dark');
}

function apply(dark, persist = true) {
    document.documentElement.classList.toggle('dark', dark);
    document.documentElement.style.colorScheme = dark ? 'dark' : 'light';

    document.querySelectorAll('[data-theme-toggle]').forEach((toggle) => {
        toggle.setAttribute('aria-pressed', dark ? 'true' : 'false');
    });

    if (persist) {
        try {
            localStorage.setItem(STORAGE_KEY, dark ? 'dark' : 'light');
        } catch (error) {
            /* storage unavailable */
        }
    }
}

export function initTheme() {
    const toggles = document.querySelectorAll('[data-theme-toggle]');

    apply(isDark(), false);

    toggles.forEach((toggle) => {
        toggle.addEventListener('click', () => apply(!isDark()));
    });

    const media = window.matchMedia('(prefers-color-scheme: dark)');
    media.addEventListener?.('change', (event) => {
        let stored = null;
        try {
            stored = localStorage.getItem(STORAGE_KEY);
        } catch (error) {
            stored = null;
        }

        if (!stored) {
            apply(event.matches, false);
        }
    });
}

export { prefersReducedMotion };
