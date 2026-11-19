export function initShare() {
    document.querySelectorAll('[data-share-copy]').forEach((button) => {
        button.addEventListener('click', async () => {
            const url = button.dataset.shareUrl || window.location.href;
            const feedback = button.closest('[data-share]')?.querySelector('[data-share-feedback]');

            const notify = (message) => {
                if (!feedback) {
                    return;
                }

                feedback.textContent = message;
                feedback.classList.remove('hidden');

                window.setTimeout(() => feedback.classList.add('hidden'), 2200);
            };

            try {
                await navigator.clipboard.writeText(url);
                notify(window.i18n?.linkCopied ?? 'Link copied');
            } catch (error) {
                window.prompt(window.i18n?.copyLink ?? 'Copy this link', url);
            }
        });
    });
}
