/**
 * Service worker: makes the app load with no network.
 *
 * IndexedDB already holds the cards and the queued ratings, but without this the
 * browser cannot even fetch the HTML and JS to show them.
 *
 * Two strategies, chosen by what breaks if the answer is stale:
 *   - assets (hashed by Vite): cache first. The filename changes on every build,
 *     so a cached hit is never wrong.
 *   - navigations: network first, falling back to the cached shell. A stale page
 *     is better than a dead tab.
 *
 * API requests are never cached. A stale queue or a stale card list would be worse
 * than no answer: /api/sync must reach the server or the ratings stay queued.
 */

const VERSION = 'v1';
const SHELL_CACHE = `memflash-shell-${VERSION}`;
const ASSET_CACHE = `memflash-assets-${VERSION}`;

const SHELL_URLS = ['/dashboard', '/offline.html'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        (async () => {
            const cache = await caches.open(SHELL_CACHE);
            // Individually, so one 404 does not abort the whole install.
            await Promise.allSettled(SHELL_URLS.map((url) => cache.add(url)));
            await self.skipWaiting();
        })()
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        (async () => {
            const keep = [SHELL_CACHE, ASSET_CACHE];
            const names = await caches.keys();
            await Promise.all(names.filter((n) => !keep.includes(n)).map((n) => caches.delete(n)));
            await self.clients.claim();
        })()
    );
});

/**
 * The dashboard asks for every study page (and the study scripts) to be kept, so a
 * deck never opened on this device can still be studied offline. Same origin
 * only, and never a redirect: a lesson with nothing due redirects to the
 * dashboard, and serving that for the study URL would be wrong.
 */
self.addEventListener('message', (event) => {
    if (event.data?.type !== 'precache' || !Array.isArray(event.data.urls)) return;

    event.waitUntil(
        (async () => {
            const cache = await caches.open(SHELL_CACHE);

            await Promise.allSettled(
                event.data.urls.map(async (url) => {
                    const target = new URL(url, self.location.origin);
                    if (target.origin !== self.location.origin) return;

                    const response = await fetch(target, { credentials: 'same-origin' });
                    if (response.ok && !response.redirected) await cache.put(target, response);
                })
            );
        })()
    );
});

const isAsset = (url) =>
    url.pathname.startsWith('/build/') || url.pathname.startsWith('/js/') || url.pathname.startsWith('/css/');

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    // Never cache the API. Sync and the queue must always hit the network.
    if (url.pathname.startsWith('/api/')) return;

    if (isAsset(url)) {
        event.respondWith(
            (async () => {
                const cached = await caches.match(request);
                if (cached) return cached;

                const response = await fetch(request);
                if (response.ok) {
                    const cache = await caches.open(ASSET_CACHE);
                    cache.put(request, response.clone());
                }
                return response;
            })()
        );
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(
            (async () => {
                try {
                    const response = await fetch(request);
                    if (response.ok) {
                        const cache = await caches.open(SHELL_CACHE);
                        cache.put(request, response.clone());
                    }
                    return response;
                } catch {
                    return (
                        (await caches.match(request)) ??
                        (await caches.match('/offline.html')) ??
                        new Response('Offline and this page was never cached.', {
                            status: 503,
                            headers: { 'Content-Type': 'text/plain' },
                        })
                    );
                }
            })()
        );
    }
});

/** Let the page ask for a sync attempt after reconnecting. */
self.addEventListener('message', (event) => {
    if (event.data?.type === 'SYNC_NOW') {
        self.clients.matchAll().then((clients) => {
            for (const client of clients) client.postMessage({ type: 'SYNC_NOW' });
        });
    }
});
