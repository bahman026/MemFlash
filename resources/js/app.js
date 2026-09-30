import './bootstrap';
import {
    autoSync,
    bootstrap,
    bootstrapIfStale,
    dueQueue,
    hasDeck,
    preview,
    rate,
    refreshDeck,
    status,
    studyQueue,
    sync,
} from './offline/sync.js';
import { formatInterval } from './offline/queue.js';

/**
 * Expose the offline API on window so the Blade study screens can use it without
 * being converted to modules. Keep it a thin surface: the logic lives in
 * offline/sync.js, offline/queue.js and fsrs/fsrs.js.
 */
window.MemFlash = { bootstrap, refreshDeck, hasDeck, sync, status, dueQueue, studyQueue, preview, rate, formatInterval };

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

// On the dashboard, keep every deck downloaded for offline study. The page lists
// the study scripts to keep alongside the study pages themselves.
const offlineAssets = document.querySelector('meta[name="memflash-offline-assets"]');
if (offlineAssets && 'indexedDB' in window) {
    window.addEventListener('load', () => {
        const urls = offlineAssets.getAttribute('content').split(' ').filter(Boolean);
        bootstrapIfStale(urls).catch((error) => console.warn('Offline download deferred:', error.message));
    });
}
