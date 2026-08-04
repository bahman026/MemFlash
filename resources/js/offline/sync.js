/**
 * Offline study orchestration.
 *
 * The flow the product needs: download once, study with no network, sync when the
 * connection comes back.
 *
 *   1. bootstrap()  pulls every deck, card and memory state into IndexedDB
 *   2. rate()       schedules locally with the JS mirror and queues the raw rating
 *   3. sync()       posts the queue; the server replays it and its answer wins
 *
 * The server is authoritative. The local scheduler exists so a session stays
 * usable and the answer buttons can show intervals, not to be the source of
 * truth. Every sync overwrites local state with what the server computed.
 */

import { Scheduler, CardState } from '../fsrs/fsrs.js';
import {
    cardKey,
    putCards,
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

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

const headers = () => ({
    'Content-Type': 'application/json',
    Accept: 'application/json',
    'X-CSRF-TOKEN': csrf(),
    'X-Requested-With': 'XMLHttpRequest',
});

/** crypto.randomUUID needs a secure context; fall back for plain http on a LAN. */
function uuid() {
    if (globalThis.crypto?.randomUUID) return globalThis.crypto.randomUUID();

    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
        const r = (Math.random() * 16) | 0;
        const v = c === 'x' ? r : (r & 0x3) | 0x8;
        return v.toString(16);
    });
}

/**
 * Download everything needed to study offline.
 *
 * Deliberately one request and a full replace: a client about to lose
 * connectivity cannot paginate or reconcile deltas.
 */
export async function bootstrap() {
    const response = await fetch('/api/sync/bootstrap', { headers: headers() });

    if (!response.ok) throw new Error(`Bootstrap failed: ${response.status}`);

    const payload = await response.json();
    const cards = [];
    const configs = {};

    for (const deck of payload.decks) {
        configs[`card:${deck.id}`] = deck.config;
        for (const card of deck.cards) {
            cards.push({ ...card, key: cardKey('card', card.id), type: 'card', deckId: deck.id });
        }
    }

    for (const deck of payload.static_decks) {
        configs[`static_card:${deck.id}`] = { ...deck.config, cardsPerDay: deck.cards_per_day };
        for (const card of deck.cards) {
            cards.push({
                ...card,
                key: cardKey('static_card', card.id),
                type: 'static_card',
                deckId: deck.id,
            });
        }
    }

    await putCards(cards);
    await setMeta('configs', configs);
    await setMeta('user', payload.user);
    await setMeta('synced_at', payload.synced_at);

    return { cards: cards.length, decks: payload.decks.length + payload.static_decks.length };
}

async function schedulerFor(type, deckId) {
    const configs = (await getMeta('configs')) ?? {};
    const user = (await getMeta('user')) ?? {};
    const config = configs[`${type}:${deckId}`] ?? {};

    return new Scheduler({ ...config, rolloverHour: user.rollover_hour ?? 4 });
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
 * Rate a card while offline.
 *
 * Applies the local schedule immediately so the session can continue, and queues
 * the RAW rating -- never the computed state. The server recomputes from the
 * rating and timestamp, so a mirror that drifts cannot corrupt stored scheduling.
 */
export async function rate(card, rating, reviewDurationMs = null) {
    const scheduler = await schedulerFor(card.type, card.deckId);
    const now = new Date();
    const outcome = scheduler.review(toMemory(card), rating, now);

    await updateCard(card.type, card.id, {
        state: outcome.state,
        step: outcome.step,
        stability: outcome.stability,
        difficulty: outcome.difficulty,
        due: outcome.due.toISOString(),
        last_review: outcome.lastReview.toISOString(),
        reps: outcome.reps,
        lapses: outcome.lapses,
    });

    await enqueue({
        clientUuid: uuid(),
        type: card.type,
        card_id: card.id,
        rating,
        reviewed_at: now.toISOString(),
        review_duration_ms: reviewDurationMs,
    });

    return outcome;
}

/**
 * Push the queue and adopt the server's answer.
 *
 * Entries are removed only once the server confirms them, so an interrupted sync
 * leaves them queued for the next attempt. Retrying is safe because the server
 * dedupes on clientUuid.
 */
export async function sync() {
    if (!navigator.onLine) return { skipped: 'offline' };

    const pending = await queued();
    if (pending.length === 0) return { applied: 0, rejected: 0 };

    const response = await fetch('/api/sync', {
        method: 'POST',
        headers: headers(),
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

/** Sync on reconnect and on load. Safe to call more than once. */
export function autoSync() {
    const attempt = () => sync().catch((error) => console.warn('Sync deferred:', error.message));

    window.addEventListener('online', attempt);
    if (navigator.onLine) attempt();
}
