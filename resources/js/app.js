import './bootstrap';
import {
    autoSync,
    bootstrap,
    bootstrapIfStale,
    dueQueue,
    hasDeck,
    lookUpOnDevice,
    openDeck,
    preview,
    rate,
    refreshDeck,
    refreshDictionary,
    rememberWord,
    status,
    studyQueue,
    sync,
} from './offline/sync.js';
import { formatInterval } from './offline/queue.js';
import { translateInBrowser } from './offline/google.js';

/**
 * Expose the offline API on window so the Blade study screens can use it without
 * being converted to modules. Keep it a thin surface: the logic lives in
 * offline/sync.js, offline/queue.js and fsrs/fsrs.js.
 */
window.MemFlash = {
    bootstrap,
    refreshDeck,
    hasDeck,
    sync,
    status,
    dueQueue,
    studyQueue,
    preview,
    rate,
    formatInterval,
    lookUpOnDevice,
    refreshDictionary,
    rememberWord,
    translateInBrowser,
};

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

// Keep decks on the device. A deck's page downloads that deck as it is opened, so
// it opens and studies offline from then on; the dashboard downloads all of them.
// Both list the study scripts to keep alongside the pages.
const offlineAssets = document.querySelector('meta[name="memflash-offline-assets"]');
const offlineDeck = document.querySelector('meta[name="memflash-offline-deck"]');
if (offlineAssets && 'indexedDB' in window) {
    window.addEventListener('load', () => {
        const urls = offlineAssets.getAttribute('content').split(' ').filter(Boolean);
        const [type, id] = offlineDeck?.getAttribute('content').split(':') ?? [];
        const download = offlineDeck ? openDeck(type, Number(id), urls) : bootstrapIfStale(urls);
        download.catch((error) => console.warn('Offline download deferred:', error.message));
    });
}

// Say so while offline: the page may be the copy kept on the device.
const offlineNotice = document.querySelector('[data-offline-notice]');
if (offlineNotice) {
    const showOffline = () => {
        offlineNotice.hidden = navigator.onLine;
    };
    window.addEventListener('online', showOffline);
    window.addEventListener('offline', showOffline);
    showOffline();
}
