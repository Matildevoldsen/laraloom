const matchingActions = (sourceForm) => Array.from(document.querySelectorAll('[data-post-action]'))
    .filter((form) => form instanceof HTMLFormElement
        && form.dataset.postId === sourceForm.dataset.postId
        && form.dataset.action === sourceForm.dataset.action);

const actionButton = (form) => {
    const button = form.querySelector('[data-post-action-button]');

    return button instanceof HTMLButtonElement ? button : null;
};

const actionCount = (button) => {
    const count = button.querySelector('[data-post-action-count]');

    if (!(count instanceof HTMLElement)) {
        return null;
    }

    const value = Number.parseInt(count.textContent ?? '0', 10);

    return Number.isNaN(value) ? 0 : value;
};

const renderAction = (form, active, count = null) => {
    const button = actionButton(form);

    if (!button) {
        return;
    }

    button.classList.toggle('is-active', active);
    button.setAttribute('aria-pressed', active ? 'true' : 'false');
    button.setAttribute('aria-label', active ? button.dataset.activeLabel : button.dataset.inactiveLabel);

    const inactiveIcon = button.querySelector('[data-post-action-inactive-icon]');
    const activeIcon = button.querySelector('[data-post-action-active-icon]');
    inactiveIcon?.classList.toggle('hidden', active);
    activeIcon?.classList.toggle('hidden', !active);

    const countElement = button.querySelector('[data-post-action-count]');
    if (countElement instanceof HTMLElement && Number.isInteger(count)) {
        countElement.textContent = String(Math.max(0, count));
    }
};

const renderMatchingActions = (sourceForm, active, count = null) => {
    matchingActions(sourceForm).forEach((form) => renderAction(form, active, count));
};

const submitAction = async (form) => {
    const button = actionButton(form);

    if (!button || form.dataset.pending === 'true') {
        return;
    }

    const previousActive = button.getAttribute('aria-pressed') === 'true';
    const previousCount = actionCount(button);
    const optimisticCount = previousCount === null
        ? null
        : previousCount + (previousActive ? -1 : 1);

    form.dataset.pending = 'true';
    button.disabled = true;
    button.setAttribute('aria-busy', 'true');
    renderMatchingActions(form, !previousActive, optimisticCount);

    try {
        const headers = {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };
        const socketId = window.Echo?.socketId();

        if (socketId) {
            headers['X-Socket-ID'] = socketId;
        }

        const response = await fetch(form.action, {
            method: form.method,
            body: new FormData(form),
            credentials: 'same-origin',
            headers,
        });
        const result = await response.json();

        if (!response.ok || typeof result.active !== 'boolean' || !Number.isInteger(result.count)) {
            throw new Error(`Post action failed with ${response.status}.`);
        }

        renderMatchingActions(form, result.active, result.count);
    } catch {
        renderMatchingActions(form, previousActive, previousCount);
        announceFailure();
    } finally {
        form.dataset.pending = 'false';
        button.disabled = false;
        button.removeAttribute('aria-busy');
    }
};

export const initializePostActions = () => {
    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || !form.matches('[data-post-action]')) {
            return;
        }

        event.preventDefault();
        void submitAction(form);
    });
};
let noticeTimeout;

const announceFailure = () => {
    let notice = document.querySelector('[data-post-action-notice]');

    if (!(notice instanceof HTMLElement)) {
        notice = document.createElement('div');
        notice.className = 'post-action-notice';
        notice.dataset.postActionNotice = 'true';
        notice.setAttribute('role', 'status');
        notice.setAttribute('aria-live', 'polite');
        document.body.append(notice);
    }

    notice.textContent = 'We couldn\u2019t update that post. Please try again.';
    notice.classList.add('is-visible');
    window.clearTimeout(noticeTimeout);
    noticeTimeout = window.setTimeout(() => notice.classList.remove('is-visible'), 4000);
};
