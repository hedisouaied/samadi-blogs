function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function setMessage(element, message, ok = true) {
    if (!element) {
        return;
    }

    element.textContent = message;
    element.classList.remove('hidden');
    element.style.color = ok ? '' : 'rgb(124 34 51)';
}

async function post(form) {
    const response = await fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken(),
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: new FormData(form),
    });

    let payload = {};

    try {
        payload = await response.json();
    } catch (error) {
        payload = {};
    }

    return { ok: response.ok, payload };
}

export function initForms() {
    initNewsletter();
    initComments();
}

function initNewsletter() {
    document.querySelectorAll('[data-newsletter]').forEach((form) => {
        const message = form.querySelector('[data-newsletter-message]');
        const label = form.querySelector('[data-newsletter-label]');

        form.addEventListener('submit', async (event) => {
            if (!form.checkValidity()) {
                return;
            }

            event.preventDefault();

            const original = label?.textContent;
            if (label) {
                label.textContent = 'Sending…';
            }

            try {
                const { ok, payload } = await post(form);
                setMessage(message, payload.message || (ok ? 'Subscribed.' : 'Something went wrong.'), ok);

                if (ok) {
                    form.reset();
                }
            } catch (error) {
                setMessage(message, 'Network error. Please try again.', false);
            } finally {
                if (label && original) {
                    label.textContent = original;
                }
            }
        });
    });
}

function initComments() {
    document.querySelectorAll('[data-reply-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const panel = document.getElementById(button.dataset.replyTarget || '');
            panel?.classList.toggle('hidden');

            if (panel && !panel.classList.contains('hidden')) {
                panel.querySelector('textarea')?.focus();
            }
        });
    });

    document.querySelectorAll('[data-comment-form]').forEach((form) => {
        const message = form.querySelector('[data-comment-message]');
        const label = form.querySelector('[data-comment-label]');

        form.addEventListener('submit', async (event) => {
            if (!form.checkValidity()) {
                return;
            }

            event.preventDefault();

            const original = label?.textContent;
            if (label) {
                label.textContent = 'Posting…';
            }

            try {
                const { ok, payload } = await post(form);
                setMessage(message, payload.message || (ok ? 'Thanks!' : 'Could not post your comment.'), ok);

                if (ok) {
                    form.reset();
                }
            } catch (error) {
                setMessage(message, 'Network error. Please try again.', false);
            } finally {
                if (label && original) {
                    label.textContent = original;
                }
            }
        });
    });
}
