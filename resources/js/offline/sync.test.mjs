/**
 * The offline study flow end to end, in node: the real sync.js, db.js and
 * queue.js against an in-memory IndexedDB, with fetch scripted to play the
 * server. What a browser does between "download once" and "sync on reconnect".
 */
import { test, beforeEach } from 'node:test';
import assert from 'node:assert/strict';

import { createFakeIndexedDB, FakeKeyRange } from './fake-indexeddb.mjs';

const idb = createFakeIndexedDB();
const setGlobal = (name, value) => Object.defineProperty(globalThis, name, { value, configurable: true, writable: true });

setGlobal('indexedDB', idb);
setGlobal('IDBKeyRange', FakeKeyRange);
setGlobal('navigator', { onLine: true });
setGlobal('document', { cookie: 'other=1; XSRF-TOKEN=fresh%3Dtoken', querySelector: () => null });

const MemFlash = await import('./sync.js');

// ---------------------------------------------------------------------------
// A scripted server
// ---------------------------------------------------------------------------

let calls = [];
let script = [];

setGlobal('fetch', async (url, options = {}) => {
    calls.push({ url, method: options.method ?? 'GET', headers: options.headers ?? {}, body: options.body ? JSON.parse(options.body) : null });
    const next = script.shift();
    if (!next) throw new Error(`Unexpected request to ${url}`);
    const [status, body] = typeof next === 'function' ? next(url, options) : next;

    return { ok: status >= 200 && status < 300, status, json: async () => body };
});

// Relative to the real clock: the code under test compares against Date.now().
const studyDayStart = (() => {
    const now = new Date();
    const start = new Date(Date.UTC(now.getUTCFullYear(), now.getUTCMonth(), now.getUTCDate(), 4));
    if (now < start) start.setUTCDate(start.getUTCDate() - 1);
    return start.toISOString();
})();
const daysAgo = (days) => new Date(Date.now() - days * 86_400_000).toISOString();
const today = studyDayStart;
const past = daysAgo(1);

const personalDeck = (cards, extra = {}) => ({
    id: 1,
    name: 'Vocabulary 5B',
    new_cards_per_day: 2,
    new_cards_today: 1,
    config: { learningSteps: [60, 600], relearningSteps: [600], enableFuzzing: false },
    cards,
    ...extra,
});
const newCard = (id) => ({ id, front: `word ${id}`, back: `meaning ${id}`, description: null, state: 'new', step: null, stability: null, difficulty: null, due: null, last_review: null, reps: 0, lapses: 0, suspended: false });
const reviewCard = (id) => ({ ...newCard(id), state: 'review', stability: 10, difficulty: 5, due: past, last_review: daysAgo(11), reps: 3 });

const payload = (decks, staticDecks = []) => ({
    synced_at: '2026-09-30T08:00:00+00:00',
    user: { id: 7, timezone: 'UTC', rollover_hour: 4, study_day_started_at: today },
    decks,
    static_decks: staticDecks,
});

const lesson = { id: 9, name: 'Lesson 1', cards_per_day: 10, new_cards_today: 0, config: {}, cards: [{ ...newCard(50), audio: null }] };

beforeEach(() => {
    idb.reset();
    calls = [];
    script = [];
    navigator.onLine = true;
});

// ---------------------------------------------------------------------------

test('downloads every deck and studies it with the daily new-card limit', async () => {
    script.push([200, payload([personalDeck([newCard(1), newCard(2), newCard(3), reviewCard(4)])], [lesson])]);

    const result = await MemFlash.bootstrap();

    assert.equal(result.cards, 5);
    assert.deepEqual(result.studyUrls, ['/study/1', '/static-decks/9/study']);
    assert.equal(calls[0].url, '/api/sync/bootstrap');

    // Limit 2, one new card already started today on the server: the due review
    // plus one new card.
    const queue = await MemFlash.studyQueue('card', 1);
    assert.deepEqual(queue.map((c) => c.id), [4, 1]);
    assert.equal(await MemFlash.hasDeck('card', 1), true);
    assert.equal(await MemFlash.hasDeck('card', 2), false);
});

test('studies offline, keeps the answer on the device, and syncs it later', async () => {
    script.push([200, payload([personalDeck([newCard(1), newCard(2), reviewCard(4)])])]);
    await MemFlash.bootstrap();

    navigator.onLine = false;
    const [review, fresh] = await MemFlash.studyQueue('card', 1);

    const outcome = await MemFlash.rate(fresh, 3, 1500);
    assert.equal(outcome.state, 'learning');
    assert.equal(outcome.scheduledSeconds, 600); // Good on a new card: step 1
    assert.equal(outcome.card.state, 'learning');
    assert.equal(outcome.card.reps, 1);

    // The limit is used up offline too, and the rated card is not due for minutes.
    assert.deepEqual((await MemFlash.studyQueue('card', 1)).map((c) => c.id), [review.id]);
    assert.equal((await MemFlash.status()).pending, 1);

    // No network: nothing is sent.
    assert.deepEqual(await MemFlash.sync(), { skipped: 'offline' });
    assert.equal(calls.length, 1);

    // Back online: the raw rating goes to the server with its original time...
    navigator.onLine = true;
    script.push((url, options) => {
        const [entry] = JSON.parse(options.body).reviews;
        return [200, {
            synced_at: '2026-09-30T09:00:00+00:00',
            applied: [{ client_uuid: entry.client_uuid, type: 'card', card_id: 1, state: 'learning', step: 1, stability: 2.3065, difficulty: 2.1181, due: '2026-09-30T09:10:00+00:00', last_review: entry.reviewed_at, reps: 1, lapses: 0 }],
            rejected: [],
        }];
    });

    assert.deepEqual(await MemFlash.sync(), { applied: 1, rejected: 0 });

    const sent = calls[1];
    assert.equal(sent.url, '/api/sync');
    assert.equal(sent.method, 'POST');
    assert.equal(sent.headers['X-XSRF-TOKEN'], 'fresh=token');
    assert.deepEqual(Object.keys(sent.body.reviews[0]).sort(), ['card_id', 'client_uuid', 'rating', 'review_duration_ms', 'reviewed_at', 'type']);
    assert.equal(sent.body.reviews[0].rating, 3);
    assert.equal(sent.body.reviews[0].review_duration_ms, 1500);

    // ...and the server's state replaces the local one.
    assert.equal((await MemFlash.status()).pending, 0);
    const [stored] = (await MemFlash.dueQueue('card', 1)).filter((c) => c.id === 1);
    assert.equal(stored, undefined); // learning until 09:10, not due now
});

test('keeps the answer queued when the sync fails', async () => {
    script.push([200, payload([personalDeck([newCard(1)])])]);
    await MemFlash.bootstrap();
    await MemFlash.rate((await MemFlash.studyQueue('card', 1))[0], 1);

    script.push([500, {}]);
    await assert.rejects(MemFlash.sync(), /Sync failed: 500/);

    assert.equal((await MemFlash.status()).pending, 1);
});

test('retries once with a fresh session after a 419', async () => {
    script.push([200, payload([personalDeck([newCard(1)])])]);
    await MemFlash.bootstrap();
    await MemFlash.rate((await MemFlash.studyQueue('card', 1))[0], 3);

    script.push([419, {}]);
    script.push([200, { online: true }]); // the status GET that renews the session
    script.push((url, options) => {
        const [entry] = JSON.parse(options.body).reviews;
        return [200, { synced_at: 'now', applied: [], rejected: [{ client_uuid: entry.client_uuid, reason: 'x' }] }];
    });

    assert.deepEqual(await MemFlash.sync(), { applied: 0, rejected: 1 });
    assert.deepEqual(calls.slice(-3).map((c) => `${c.method} ${c.url}`), ['POST /api/sync', 'GET /api/sync/status', 'POST /api/sync']);
    assert.equal((await MemFlash.status()).pending, 0);
});

test('refreshes one deck without touching the others', async () => {
    script.push([200, payload([personalDeck([newCard(1), newCard(2)])], [lesson])]);
    await MemFlash.bootstrap();

    script.push([200, payload([personalDeck([newCard(3)], { new_cards_today: 0 })])]);
    assert.deepEqual(await MemFlash.refreshDeck('card', 1), { found: true, cards: 1 });
    assert.equal(calls[1].url, '/api/sync/bootstrap?deck=card:1');

    assert.deepEqual((await MemFlash.studyQueue('card', 1)).map((c) => c.id), [3]);
    assert.deepEqual((await MemFlash.studyQueue('static_card', 9)).map((c) => c.id), [50]);

    script.push([404, {}]);
    assert.deepEqual(await MemFlash.refreshDeck('card', 99), { found: false });
});

test('shares one request between overlapping syncs', async () => {
    script.push([200, payload([personalDeck([newCard(1)])])]);
    await MemFlash.bootstrap();
    await MemFlash.rate((await MemFlash.studyQueue('card', 1))[0], 3);

    script.push([200, { synced_at: 'now', applied: [], rejected: [] }]);
    const [a, b] = await Promise.all([MemFlash.sync(), MemFlash.sync()]);

    assert.deepEqual(a, b);
    assert.equal(calls.filter((c) => c.url === '/api/sync').length, 1);
});
