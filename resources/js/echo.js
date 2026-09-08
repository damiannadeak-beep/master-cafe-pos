import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;
const reverbHost = import.meta.env.VITE_REVERB_HOST || 'localhost';
const reverbPort = import.meta.env.VITE_REVERB_PORT || 8080;
const reverbScheme = import.meta.env.VITE_REVERB_SCHEME || 'http';

const useReverb = Boolean(reverbKey && !reverbKey.startsWith('${'));

if (useReverb) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: reverbKey,
        wsHost: reverbHost,
        wsPort: reverbPort ?? 80,
        wssPort: reverbPort ?? 443,
        forceTLS: reverbScheme === 'https',
        enabledTransports: ['ws', 'wss'],
    });
} else {
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: import.meta.env.VITE_PUSHER_APP_KEY,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
        wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : 'ws-' + (import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1') + '.pusher.com',
        wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
        wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
    });
}