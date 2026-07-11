import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const key = import.meta.env.VITE_PUSHER_APP_KEY;
const refreshButton = document.querySelector('[data-realtime-refresh]');

if (key && refreshButton instanceof HTMLButtonElement) {
    window.Pusher = Pusher;

    const echo = new Echo({
        broadcaster: 'pusher',
        key,
        cluster: 'mt1',
        wsHost: import.meta.env.VITE_PUSHER_HOST ?? 'wss.vask.dev',
        wsPort: Number(import.meta.env.VITE_PUSHER_PORT ?? 443),
        wssPort: Number(import.meta.env.VITE_PUSHER_PORT ?? 443),
        forceTLS: true,
        enabledTransports: ['ws', 'wss'],
        disableStats: true,
    });

    let refreshInFlight = false;
    let refreshPending = false;

    const revealRefresh = () => {
        refreshButton.textContent = 'Live update paused · refresh';
        refreshButton.classList.remove('pointer-events-none', 'translate-y-2', 'opacity-0');
    };

    const replaceFragment = (document, selector) => {
        const current = window.document.querySelector(selector);
        const updated = document.querySelector(selector);

        if (!(current instanceof HTMLElement) || !(updated instanceof HTMLElement)) {
            return false;
        }

        current.replaceWith(updated);

        return true;
    };

    const refreshVisibleContent = async () => {
        if (refreshInFlight) {
            refreshPending = true;

            return;
        }

        refreshInFlight = true;

        try {
            const response = await fetch(window.location.href, {
                credentials: 'same-origin',
                headers: { 'X-Laraloom-Realtime': 'true' },
            });

            if (!response.ok) {
                throw new Error(`Realtime refresh failed with ${response.status}.`);
            }

            const document = new DOMParser().parseFromString(await response.text(), 'text/html');
            const refreshed = [
                replaceFragment(document, '[data-realtime-feed]'),
                replaceFragment(document, '[data-realtime-post]'),
                replaceFragment(document, '[data-realtime-conversation-summary]'),
                replaceFragment(document, '[data-realtime-comments]'),
            ].some(Boolean);

            if (!refreshed) {
                revealRefresh();
            }
        } catch {
            revealRefresh();
        } finally {
            refreshInFlight = false;

            if (refreshPending) {
                refreshPending = false;
                void refreshVisibleContent();
            }
        }
    };

    echo.channel('laraloom.feed').listen('.community.activity', refreshVisibleContent);

    const postId = refreshButton.dataset.postId;
    if (postId) {
        echo.channel(`laraloom.posts.${postId}`).listen('.community.activity', refreshVisibleContent);
    }

    refreshButton.addEventListener('click', () => window.location.reload());
}
