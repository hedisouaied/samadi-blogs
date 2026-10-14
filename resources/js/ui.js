export function initUi() {
    initBackToTop();
    initToasts();
}

function initBackToTop() {
    const button = document.querySelector('[data-back-to-top]');

    if (!button) {
        return;
    }

    const update = () => {
        button.style.display = window.scrollY > 700 ? 'inline-flex' : 'none';
    };

    update();
    window.addEventListener('scroll', update, { passive: true });

    button.addEventListener('click', () => {
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
    });
}

function initToasts() {
    document.querySelectorAll('[data-toast]').forEach((toast) => {
        const dismiss = () => {
            toast.style.transition = 'opacity 300ms ease, transform 300ms ease';
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(12px)';
            window.setTimeout(() => toast.remove(), 320);
        };

        toast.querySelector('[data-toast-close]')?.addEventListener('click', dismiss);
        window.setTimeout(dismiss, 7000);
    });
}
