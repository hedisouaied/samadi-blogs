export function initNavigation() {
    initHeaderScroll();
    initMobileNav();
    initSearchOverlay();
}

function initHeaderScroll() {
    const header = document.querySelector('[data-site-header]');
    if (!header) return;

    const update = () => {
        header.dataset.scrolled = window.scrollY > 8 ? 'true' : 'false';
    };

    update();
    window.addEventListener('scroll', update, { passive: true });
}

function initMobileNav() {
    const root = document.querySelector('[data-mobile-nav]');
    if (!root) return;

    const backdrop = root.querySelector('[data-mobile-backdrop]');
    const panel = root.querySelector('[data-mobile-panel]');
    const openers = document.querySelectorAll('[data-mobile-toggle]');
    const closers = root.querySelectorAll('[data-mobile-close]');

    let open = false;

    const show = () => {
        open = true;
        root.classList.remove('hidden');
        root.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        openers.forEach((el) => el.setAttribute('aria-expanded', 'true'));

        requestAnimationFrame(() => {
            backdrop?.classList.remove('opacity-0');
            panel?.classList.remove('translate-x-full');
        });
    };

    const hide = () => {
        open = false;
        backdrop?.classList.add('opacity-0');
        panel?.classList.add('translate-x-full');
        root.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
        openers.forEach((el) => el.setAttribute('aria-expanded', 'false'));

        window.setTimeout(() => {
            if (!open) {
                root.classList.add('hidden');
            }
        }, 500);
    };

    openers.forEach((el) => el.addEventListener('click', show));
    closers.forEach((el) => el.addEventListener('click', hide));
    backdrop?.addEventListener('click', hide);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && open) {
            hide();
        }
    });
}

function initSearchOverlay() {
    const root = document.querySelector('[data-search-overlay]');
    if (!root) return;

    const backdrop = root.querySelector('[data-search-backdrop]');
    const input = root.querySelector('[data-search-input]');
    const openers = document.querySelectorAll('[data-search-open]');
    const closers = root.querySelectorAll('[data-search-close]');

    let open = false;

    const show = () => {
        open = true;
        root.classList.remove('hidden');
        root.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');

        requestAnimationFrame(() => {
            backdrop?.classList.remove('opacity-0');
            input?.focus();
        });
    };

    const hide = () => {
        open = false;
        backdrop?.classList.add('opacity-0');
        root.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');

        window.setTimeout(() => {
            if (!open) {
                root.classList.add('hidden');
            }
        }, 300);
    };

    openers.forEach((el) => el.addEventListener('click', show));
    closers.forEach((el) => el.addEventListener('click', hide));
    backdrop?.addEventListener('click', hide);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && open) {
            hide();
        }

        if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            open ? hide() : show();
        }
    });
}
