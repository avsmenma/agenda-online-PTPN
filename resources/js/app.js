import './bootstrap';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Initialize Laravel Echo
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY || window.PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || window.PUSHER_APP_CLUSTER || 'ap1',
    wsHost: import.meta.env.VITE_PUSHER_HOST || window.PUSHER_HOST,
    wsPort: import.meta.env.VITE_PUSHER_PORT || window.PUSHER_PORT || 6001,
    wssPort: import.meta.env.VITE_PUSHER_PORT || window.PUSHER_PORT || 6001,
    forceTLS: (import.meta.env.VITE_PUSHER_SCHEME || window.PUSHER_SCHEME || 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
    encrypted: true,
});

// Export for use in other scripts
export { Echo };
