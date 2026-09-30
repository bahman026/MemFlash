/**
 * Offline study orchestration.
 *
 * The flow the product needs: download once, study with no network, sync when the
 * connection comes back.
 *
 *   1. bootstrap()    pulls every deck, card and memory state into IndexedDB;
 *      refreshDeck()  does the same for the one deck about to be studied
 *   2. studyQueue()   picks the cards to study from the device, like the server
 *   3. rate()         schedules locally with the JS mirror and queues the raw rating
 *   4. sync()         posts the queue; the server replays it and its answer wins
 *
 * The server is authoritative. The local scheduler exists so a session stays
 * usable and the answer buttons can show intervals, not to be the source of
 * truth. Every sync overwrites local state with what the server computed.
 */

import { Scheduler, CardState } from '../fsrs/fsrs.js';
import {
    cardKey,
    putCards,
    putDeckCards,
    updateCard,
    allCards,
    cardsForDeck,
    enqueue,
    queued,
    dequeue,
    queueSize,
    setMeta,
    getMeta,
} from './db.js';
import { countNewCardStarted, newCardsLeft, selectStudyQueue } from './queue.js';

/**
 * CSRF for the JSON calls.
 *
 * Prefers Laravel's XSRF-TOKEN cookie, which every response refreshes, over the
 * page's meta tag: a page served from the service worker's cache (or one with no
 * meta tag, like the dashboard) carries a token from an older session, and a
 * sync posted with it fails with 419 forever.
 */
function csrfHeaders() {
    const cookie = document.cookie.split('; ').find((c) => c.startsWith('XSRF-TOKEN='));
    if (cookie) return { 'X-XSRF-TOKEN': decodeURIComponent(cookie.slice('XSRF-TOKEN='.length)) };

    const meta = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    return meta ? { 'X-CSRF-TOKEN': meta } : {};
}

const headers = () => ({
    'Content-Type': 'application/json',
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    ...csrfHeaders(),
});

/**
 * fetch() with one retry after a 419. The GET in between hands out a fresh
 * session (the remember-me cookie signs the user back in) and with it a fresh
 * XSRF-TOKEN cookie.
 */
async function request(url, options = {}) {
    let response = await fetch(url, { ...options, headers: headers() });

    if (response.status === 419) {
        await fetch('/api/sync/status', { headers: headers() });
        response = await fetch(url, { ...options, headers: headers() });
    }

    return response;
}

/** crypto.randomUUID needs a secure context; fall back for plain http on a LAN. */
function uuid() {
    if (globalThis.crypto?.randomUUID) return globalThis.crypto.randomUUID();

    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
        const r = (Math.random() * 16) | 0;
        const v = c === 'x' ? r : (r & 0x3) | 0x8;
        return v.toString(16);
    });
}

const deckKey = (type, deckId) => `${type}:${deckId}`;

/** Flatten a bootstrap payload into card rows, scheduler presets and deck records. */
function readPayload(payload) {
    const cards = [];
    const configs = {};
    const decks = {};
    const dayStartedAt = payload.user?.study_day_started_at ?? payload.synced_at;

    for (const deck of payload.decks) {
        configs[deckKey('card', deck.id)] = deck.config;
        decks[deckKey('card', deck.id)] = {
            name: deck.name,
            limit: deck.new_cards_per_day ?? 10,
            startedToday: deck.new_cards_today ?? 0,
            dayStartedAt,
        };
        for (const card of deck.cards) {
            cards.push({ ...card, key: cardKey('card', card.id), type: 'card', deckId: deck.id });
        }
    }

    for (const deck of payload.static_decks) {
        configs[deckKey('static_card', deck.id)] = { ...deck.config, cardsPerDay: deck.cards_per_day };
        decks[deckKey('static_card', deck.id)] = {
            name: deck.name,
            limit: deck.cards_per_day ?? 10,
            startedToday: deck.new_cards_today ?? 0,
            dayStartedAt,
        };
        for (const card of deck.cards) {
            cards.push({
                ...card,
                key: cardKey('static_card', card.id),
                type: 'static_card',
                deckId: deck.id,
            });
        }
    }

    return { cards, configs, decks };
}

/**
 * Download everything needed to study offline.
 *
 * Deliberately one request and a full replace: a client about to lose
 * connectivity cannot paginate or reconcile deltas. Only call it with an empty
 * queue, or unsynced local state is overwritten.
 */
export async function bootstrap() {
    const response = await request('/api/sync/bootstrap');

    if (!response.ok) throw new Error(`Bootstrap failed: ${response.status}`);

    const payload = await response.json();
    const { cards, configs, decks } = readPayload(payload);

    await putCards(cards);
    await setMeta('configs', configs);
    await setMeta('decks', decks);
    await setMeta('user', payload.user);
    await setMeta('synced_at', payload.synced_at);
    await setMeta('bootstrapped_at', new Date().toISOString());

    return {
        cards: cards.length,
        decks: payload.decks.length + payload.static_decks.length,
        studyUrls: [
            ...payload.decks.map((d) => `/study/${d.id}`),
            ...payload.static_decks.map((d) => `/static-decks/${d.id}/study`),
        ],
    };
}

/**
 * Re-download one deck and replace its copy on the device.
 *
 * Resolves to { found: false } when the server will not hand it over (someone
 * else's public deck); the study screen then studies it online only.
 */
export async function refreshDeck(type, deckId) {
    const response = await request(`/api/sync/bootstrap?deck=${type}:${deckId}`);

    if (response.status === 404) return { found: false };
    if (!response.ok) throw new Error(`Refresh failed: ${response.status}`);

    const payload = await response.json();
    const { cards, configs, decks } = readPayload(payload);

    await putDeckCards(type, deckId, cards);
    await setMeta('configs', { ...((await getMeta('configs')) ?? {}), ...configs });
    await setMeta('decks', { ...((await getMeta('decks')) ?? {}), ...decks });
    await setMeta('user', payload.user);

    return { found: true, cards: cards.length };
}

/** Whether this deck has been downloaded to the device. */
export async function hasDeck(type, deckId) {
    const decks = (await getMeta('decks')) ?? {};

    return deckKey(type, deckId) in decks || (await cardsForDeck(type, deckId)).length > 0;
}

async function schedulerFor(type, deckId) {
    const configs = (await getMeta('configs')) ?? {};
    const user = (await getMeta('user')) ?? {};
    const config = configs[deckKey(type, deckId)] ?? {};

    // Both matter for day-boundary math (Scheduler.dayDifference): rolloverHour
    // alone is meaningless without knowing which timezone it is 4am IN. Omitting
    // timezone here used to leave the mirror on its UTC default regardless of the
    // user's actual setting.
    return new Scheduler({ ...config, rolloverHour: user.rollover_hour ?? 4, timezone: user.timezone ?? 'UTC' });
}

/** Cards due now for a deck, in queue order, capped like the server caps it. */
export async function dueQueue(type, deckId, limit = null) {
    const cards = await cardsForDeck(type, deckId);
    const now = Date.now();

    const rank = (card) => {
        if (card.state === CardState.Learning || card.state === CardState.Relearning) return 0;
        if (card.state === CardState.Review) return 1;
        return 2;
    };

    const due = cards
        .filter((c) => !c.suspended && (!c.due || new Date(c.due).getTime() <= now))
        .sort((a, b) => rank(a) - rank(b) || new Date(a.due ?? 0) - new Date(b.due ?? 0) || a.id - b.id);

    return limit ? due.slice(0, limit) : due;
}

/**
 * The cards to study now, picked on the device the way the server picks them:
 * every due review, then new cards up to what is left of today's limit.
 */
export async function studyQueue(type, deckId) {
    const now = Date.now();
    const scheduler = await schedulerFor(type, deckId);
    const deck = ((await getMeta('decks')) ?? {})[deckKey(type, deckId)];

    return selectStudyQueue(await cardsForDeck(type, deckId), newCardsLeft(scheduler, deck, now), now);
}

/** What each button would schedule, for the answer labels. */
export async function preview(card) {
    const scheduler = await schedulerFor(card.type, card.deckId);

    return scheduler.preview(toMemory(card));
}

const toMemory = (card) => ({
    state: card.state,
    step: card.step,
    stability: card.stability,
    difficulty: card.difficulty,
    lastReview: card.last_review,
    reps: card.reps,
    lapses: card.lapses,
});

/**
 * Rate a card on the device.
 *
 * Applies the local schedule immediately so the session can continue, and queues
 * the RAW rating -- never the computed state. The server recomputes from the
 * rating and timestamp, so a mirror that drifts cannot corrupt stored scheduling.
 *
 * Resolves to the scheduler's outcome plus `card`, the card as now stored, so a
 * card that comes back later in the session is graded from its new state.
 */
export async function rate(card, rating, reviewDurationMs = null) {
    const scheduler = await schedulerFor(card.type, card.deckId);
    const now = new Date();
    const outcome = scheduler.review(toMemory(card), rating, now);

    const patch = {
        state: outcome.state,
        step: outcome.step,
        stability: outcome.stability,
        difficulty: outcome.difficulty,
        due: outcome.due.toISOString(),
        last_review: outcome.lastReview.toISOString(),
        reps: outcome.reps,
        lapses: outcome.lapses,
    };

    await updateCard(card.type, card.id, patch);

    await enqueue({
        clientUuid: uuid(),
        type: card.type,
        card_id: card.id,
        rating,
        reviewed_at: now.toISOString(),
        review_duration_ms: reviewDurationMs,
    });

    // A first review uses up one of today's new cards, offline as well as online.
    if (outcome.stateBefore === CardState.New) {
        const decks = (await getMeta('decks')) ?? {};
        const key = deckKey(card.type, card.deckId);
        decks[key] = countNewCardStarted(scheduler, decks[key] ?? {}, now.getTime());
        await setMeta('decks', decks);
    }

    return { ...outcome, card: { ...card, ...patch } };
}

/**
 * Push the queue and adopt the server's answer.
 *
 * Entries are removed only once the server confirms them, so an interrupted sync
 * leaves them queued for the next attempt. Retrying is safe because the server
 * dedupes on clientUuid. Concurrent calls share one request.
 */
let inFlight = null;

export function sync() {
    inFlight ??= pushQueue().finally(() => {
        inFlight = null;
    });

    return inFlight;
}

async function pushQueue() {
    if (!navigator.onLine) return { skipped: 'offline' };

    const pending = await queued();
    if (pending.length === 0) return { applied: 0, rejected: 0 };

    const response = await request('/api/sync', {
        method: 'POST',
        body: JSON.stringify({
            reviews: pending.map((r) => ({
                client_uuid: r.clientUuid,
                type: r.type,
                card_id: r.card_id,
                rating: r.rating,
                reviewed_at: r.reviewed_at,
                review_duration_ms: r.review_duration_ms,
            })),
        }),
    });

    if (!response.ok) throw new Error(`Sync failed: ${response.status}`);

    const payload = await response.json();

    // The server's state replaces whatever the mirror computed.
    for (const applied of payload.applied) {
        await updateCard(applied.type, applied.card_id, {
            state: applied.state,
            step: applied.step,
            stability: applied.stability,
            difficulty: applied.difficulty,
            due: applied.due,
            last_review: applied.last_review,
            reps: applied.reps,
            lapses: applied.lapses,
        });
    }

    // Drop confirmed entries, and rejected ones too: they will never succeed, so
    // keeping them would retry forever.
    await dequeue([
        ...payload.applied.map((a) => a.client_uuid),
        ...payload.rejected.map((r) => r.client_uuid),
    ]);

    await setMeta('synced_at', payload.synced_at);

    return { applied: payload.applied.length, rejected: payload.rejected.length };
}

export async function status() {
    return {
        online: navigator.onLine,
        pending: await queueSize(),
        syncedAt: await getMeta('synced_at'),
        cards: (await allCards()).length,
    };
}

/**
 * Ask the service worker to keep these pages, so a deck never opened on this
 * device can still be studied offline.
 */
export async function precache(urls) {
    if (!('serviceWorker' in navigator) || urls.length === 0) return;

    const registration = await navigator.serviceWorker.ready;
    registration.active?.postMessage({ type: 'precache', urls });
}

/**
 * Keep the device's copy of every deck reasonably fresh.
 *
 * Runs in the background from the dashboard: at most every `maxAgeMs`, only
 * online, and only once the queue is empty -- a full download replaces local
 * state, which would drop answers the server has not seen yet.
 */
export async function bootstrapIfStale(extraUrls = [], maxAgeMs = 6 * 60 * 60 * 1000) {
    if (!navigator.onLine) return { skipped: 'offline' };

    await sync();
    if ((await queueSize()) > 0) return { skipped: 'pending' };

    const last = Date.parse((await getMeta('bootstrapped_at')) ?? '');
    if (Number.isFinite(last) && Date.now() - last < maxAgeMs) return { skipped: 'fresh' };

    const result = await bootstrap();
    await precache([...result.studyUrls, ...extraUrls]);

    return result;
}

/** Sync on reconnect and on load. Safe to call more than once. */
export function autoSync() {
    const attempt = () => sync().catch((error) => console.warn('Sync deferred:', error.message));

    window.addEventListener('online', attempt);
    if (navigator.onLine) attempt();
}
