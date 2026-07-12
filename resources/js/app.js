import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const key = import.meta.env.VITE_PUSHER_APP_KEY;
const refreshButton = document.querySelector('[data-realtime-refresh]');

const scrollMessageThread = () => {
    const messageThread = document.querySelector('[data-message-scroll]');

    if (messageThread instanceof HTMLElement) {
        messageThread.scrollTop = messageThread.scrollHeight;
    }
};

const markVisibleConversationRead = async () => {
    const form = document.querySelector('[data-mark-read]');

    if (!(form instanceof HTMLFormElement) || form.dataset.pending === 'true') {
        return;
    }

    const csrfToken = form.querySelector('input[name="_token"]');
    if (!(csrfToken instanceof HTMLInputElement)) {
        return;
    }

    form.dataset.pending = 'true';
    try {
        const response = await fetch(form.action, {
            method: 'PUT',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.value,
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error(`Mark read failed with ${response.status}.`);
        }

        form.remove();
        document.querySelectorAll('[data-unread-indicator]').forEach((indicator) => indicator.remove());
    } catch {
        form.dataset.pending = 'false';
    }
};

const initializeInfiniteFeed = () => {
    const sentinel = document.querySelector('[data-infinite-feed]');

    if (!(sentinel instanceof HTMLElement) || sentinel.dataset.loading === 'true') {
        return;
    }

    const observer = new IntersectionObserver(async ([entry]) => {
        if (!entry?.isIntersecting || sentinel.dataset.loading === 'true') {
            return;
        }

        sentinel.dataset.loading = 'true';
        const response = await fetch(sentinel.dataset.nextUrl, {
            credentials: 'same-origin',
            headers: { 'X-Sourcefolk-Infinite': 'true' },
        });

        if (!response.ok) {
            sentinel.dataset.loading = 'false';

            return;
        }

        const page = new DOMParser().parseFromString(await response.text(), 'text/html');
        const currentFeed = document.querySelector('[data-realtime-feed]');
        const nextFeed = page.querySelector('[data-realtime-feed]');
        const nextSentinel = page.querySelector('[data-infinite-feed]');

        if (currentFeed instanceof HTMLElement && nextFeed instanceof HTMLElement) {
            currentFeed.append(...nextFeed.children);
        }

        sentinel.replaceWith(nextSentinel ?? document.createComment('Feed complete'));
        observer.disconnect();
        initializeInfiniteFeed();
    }, { rootMargin: '500px 0px' });

    observer.observe(sentinel);
};

initializeInfiniteFeed();
scrollMessageThread();
void markVisibleConversationRead();

if (key && refreshButton instanceof HTMLButtonElement) {
    window.Pusher = Pusher;

    const echo = window.Echo = new Echo({
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
                headers: { 'X-Sourcefolk-Realtime': 'true' },
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
                replaceFragment(document, '[data-realtime-profile]'),
                replaceFragment(document, '[data-direct-messages]'),
            ].some(Boolean);

            scrollMessageThread();
            void markVisibleConversationRead();

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

    echo.channel('sourcefolk.feed').listen('.community.activity', refreshVisibleContent);

    const postId = refreshButton.dataset.postId;
    if (postId) {
        echo.channel(`sourcefolk.posts.${postId}`).listen('.community.activity', refreshVisibleContent);
    }

    const profileId = refreshButton.dataset.profileId;
    if (profileId) {
        echo.channel(`sourcefolk.profiles.${profileId}`).listen('.follow.changed', refreshVisibleContent);
    }

    const directMessages = document.querySelector('[data-direct-messages]');
    if (directMessages instanceof HTMLElement && directMessages.dataset.userId) {
        echo.private(`sourcefolk.users.${directMessages.dataset.userId}.messages`)
            .listen('.message.created', refreshVisibleContent);
    }

    refreshButton.addEventListener('click', () => window.location.reload());
}
