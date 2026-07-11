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

    const revealRefresh = () => {
        refreshButton.classList.remove('pointer-events-none', 'translate-y-2', 'opacity-0');
    };

    echo.channel('laraloom.feed').listen('.community.activity', revealRefresh);

    const postId = refreshButton.dataset.postId;
    if (postId) {
        echo.channel(`laraloom.posts.${postId}`).listen('.community.activity', revealRefresh);
    }

    refreshButton.addEventListener('click', () => window.location.reload());
}
