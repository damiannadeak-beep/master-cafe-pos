import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;
const isHttps = typeof window !== 'undefined' && window.location.protocol === 'https:';
const reverbHost = (typeof window !== 'undefined' && window.location.hostname) ? window.location.hostname : (import.meta.env.VITE_REVERB_HOST || 'localhost');
const reverbPort = isHttps ? 443 : (import.meta.env.VITE_REVERB_PORT || 8080);
const reverbScheme = isHttps ? 'https' : (import.meta.env.VITE_REVERB_SCHEME || 'http');

const useReverb = Boolean(reverbKey && !reverbKey.startsWith('${'));

const shouldConnect = typeof window !== 'undefined' && (
    window.enableEcho === true ||
    window.location.pathname.startsWith('/kasir') ||
    window.location.pathname.startsWith('/admin')
);

if (shouldConnect) {
    if (useReverb) {
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: reverbKey,
            wsHost: reverbHost,
            wsPort: reverbPort ?? (isHttps ? 443 : 80),
            wssPort: isHttps ? 443 : (reverbPort ?? 443),
            forceTLS: isHttps || reverbScheme === 'https',
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
}