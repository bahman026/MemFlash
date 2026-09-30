/**
 * IndexedDB store for offline study.
 *
 * Three object stores:
 *   cards   the working set downloaded from /api/sync/bootstrap
 *   queue   ratings made offline, waiting to be replayed on the server
 *   meta    last sync time, the user's timezone and rollover hour
 *
 * localStorage is not used: it is synchronous, size-limited, and would block the
 * main thread while writing a few thousand cards.
 */

const DB_NAME = 'memflash';
const DB_VERSION = 1;

export const STORE_CARDS = 'cards';
export const STORE_QUEUE = 'queue';
export const STORE_META = 'meta';

let dbPromise = null;

export function openDb() {
    if (dbPromise) return dbPromise;

    dbPromise = new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, DB_VERSION);

        request.onupgradeneeded = (event) => {
            const db = event.target.result;

            if (!db.objectStoreNames.contains(STORE_CARDS)) {
                // Key is `${type}:${id}` so personal and static cards cannot collide.
                const cards = db.createObjectStore(STORE_CARDS, { keyPath: 'key' });
                cards.createIndex('deck', ['type', 'deckId'], { unique: false });
                cards.createIndex('due', 'due', { unique: false });
            }

            if (!db.objectStoreNames.contains(STORE_QUEUE)) {
                // clientUuid is the idempotency key the server dedupes on.
                db.createObjectStore(STORE_QUEUE, { keyPath: 'clientUuid' });
            }

            if (!db.objectStoreNames.contains(STORE_META)) {
                db.createObjectStore(STORE_META, { keyPath: 'key' });
            }
        };

        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });

    return dbPromise;
}

async function tx(storeName, mode, fn) {
    const db = await openDb();

    return new Promise((resolve, reject) => {
        const transaction = db.transaction(storeName, mode);
        const store = transaction.objectStore(storeName);
        let result;

        try {
            result = fn(store);
        } catch (error) {
            reject(error);
            return;
        }

        transaction.oncomplete = () => resolve(result);
        transaction.onerror = () => reject(transaction.error);
        transaction.onabort = () => reject(transaction.error);
    });
}

const request = (req) =>
    new Promise((resolve, reject) => {
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });

export const cardKey = (type, id) => `${type}:${id}`;

/** Replace the whole working set. Called after a bootstrap. */
export async function putCards(cards) {
    await tx(STORE_CARDS, 'readwrite', (store) => {
        store.clear();
        for (const card of cards) store.put(card);
    });
}

/**
 * Replace one deck's cards, leaving every other deck alone. Called when the
 * study screen refreshes the deck it is about to study.
 */
export async function putDeckCards(type, deckId, cards) {
    const db = await openDb();

    return new Promise((resolve, reject) => {
        const transaction = db.transaction(STORE_CARDS, 'readwrite');
        const store = transaction.objectStore(STORE_CARDS);
        const existing = store.index('deck').getAllKeys(IDBKeyRange.only([type, deckId]));

        existing.onsuccess = () => {
            for (const key of existing.result) store.delete(key);
            for (const card of cards) store.put(card);
        };

        transaction.oncomplete = () => resolve();
        transaction.onerror = () => reject(transaction.error);
        transaction.onabort = () => reject(transaction.error);
    });
}

/** Merge server-authoritative state over whatever was computed locally. */
export async function updateCard(type, id, patch) {
    const db = await openDb();
    const key = cardKey(type, id);

    return new Promise((resolve, reject) => {
        const transaction = db.transaction(STORE_CARDS, 'readwrite');
        const store = transaction.objectStore(STORE_CARDS);
        const get = store.get(key);

        get.onsuccess = () => {
            if (get.result) store.put({ ...get.result, ...patch });
        };

        transaction.oncomplete = () => resolve();
        transaction.onerror = () => reject(transaction.error);
    });
}

export async function allCards() {
    const db = await openDb();
    const transaction = db.transaction(STORE_CARDS, 'readonly');

    return request(transaction.objectStore(STORE_CARDS).getAll());
}

export async function cardsForDeck(type, deckId) {
    const cards = await allCards();

    return cards.filter((c) => c.type === type && c.deckId === deckId);
}

export async function enqueue(review) {
    await tx(STORE_QUEUE, 'readwrite', (store) => store.put(review));
}

export async function queued() {
    const db = await openDb();
    const transaction = db.transaction(STORE_QUEUE, 'readonly');
    const rows = await request(transaction.objectStore(STORE_QUEUE).getAll());

    // Oldest first: the server replays in order, and so should the payload.
    return rows.sort((a, b) => a.reviewed_at.localeCompare(b.reviewed_at));
}

export async function dequeue(clientUuids) {
    await tx(STORE_QUEUE, 'readwrite', (store) => {
        for (const uuid of clientUuids) store.delete(uuid);
    });
}

export async function queueSize() {
    const db = await openDb();
    const transaction = db.transaction(STORE_QUEUE, 'readonly');

    return request(transaction.objectStore(STORE_QUEUE).count());
}

export async function setMeta(key, value) {
    await tx(STORE_META, 'readwrite', (store) => store.put({ key, value }));
}

export async function getMeta(key) {
    const db = await openDb();
    const transaction = db.transaction(STORE_META, 'readonly');
    const row = await request(transaction.objectStore(STORE_META).get(key));

    return row?.value ?? null;
}
