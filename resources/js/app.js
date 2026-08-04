import './bootstrap';
import { autoSync, bootstrap, dueQueue, preview, rate, status, sync } from './offline/sync.js';

/**
 * Expose the offline API on window so the Blade study screens can use it without
 * being converted to modules. Keep it a thin surface: the logic lives in
 * offline/sync.js and fsrs/fsrs.js.
 */
window.MemFlash = { bootstrap, sync, status, dueQueue, preview, rate };

// Register the service worker so the app shell loads with no network. Skipped on
// insecure origins, where service workers are unavailable.
if ('serviceWorker' in navigator && (window.isSecureContext || location.hostname === 'localhost')) {
    window.addEventListener('load', () => {
        navigator.serviceWorker
            .register('/sw.js')
            .catch((error) => console.warn('Service worker registration failed:', error.message));
    });
}

// Push any queued reviews as soon as a connection is available.
autoSync();
