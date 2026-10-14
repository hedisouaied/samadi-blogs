export function initReadingProgress() {
    const bar = document.querySelector('[data-reading-progress]');
    const body = document.querySelector('[data-article-body]');

    if (!bar || !body) {
        return;
    }

    const update = () => {
        const start = body.offsetTop;
        const total = body.offsetHeight - window.innerHeight;
        const progress = total > 0 ? (window.scrollY - start) / total : 0;
        const clamped = Math.min(1, Math.max(0, progress));

        bar.style.transform = `scaleX(${clamped})`;
        bar.style.opacity = clamped > 0 && clamped < 1 ? '1' : clamped === 0 ? '0' : '1';
    };

    update();
    window.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update);
}
