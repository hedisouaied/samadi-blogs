export function initToc() {
    const links = Array.from(document.querySelectorAll('[data-toc-link]'));

    if (!links.length || !('IntersectionObserver' in window)) {
        return;
    }

    const mapping = new Map();

    links.forEach((link) => {
        const id = (link.getAttribute('href') || '').slice(1);
        const heading = id ? document.getElementById(id) : null;

        if (heading) {
            mapping.set(heading, link);
        }
    });

    if (!mapping.size) {
        return;
    }

    const activate = (heading) => {
        links.forEach((link) => link.classList.remove('is-active'));
        mapping.get(heading)?.classList.add('is-active');
    };

    const observer = new IntersectionObserver(
        (entries) => {
            const visible = entries
                .filter((entry) => entry.isIntersecting)
                .sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top);

            if (visible.length) {
                activate(visible[0].target);
            }
        },
        { rootMargin: '-96px 0px -65% 0px', threshold: [0, 1] },
    );

    Array.from(mapping.keys()).forEach((heading) => observer.observe(heading));
}
