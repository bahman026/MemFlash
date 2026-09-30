/**
 * Study-queue decisions for offline study.
 *
 * Pure functions: no IndexedDB, no DOM, no network. They mirror what the
 * server decides in StudyController::getCards / StaticDeckController::dueCardsFor
 * so a session studied offline shows the same cards it would have online, and
 * they are tested directly under node (queue.test.mjs).
 */

import { CardState } from '../fsrs/fsrs.js';

/** Mirrors ReviewService::MAX_REVIEWS_PER_SESSION. */
export const MAX_REVIEWS_PER_SESSION = 200;

const rank = (card) => {
    if (card.state === CardState.Learning || card.state === CardState.Relearning) return 0;
    if (card.state === CardState.Review) return 1;
    return 2;
};

// No due date sorts last among cards in progress, as `due IS NULL` does on the server.
const dueTime = (card) => (card.due ? new Date(card.due).getTime() : Number.POSITIVE_INFINITY);

/**
 * The cards to study now, in queue order.
 *
 * Due learning and relearning cards first, then due reviews by due date (at
 * most MAX_REVIEWS_PER_SESSION), then new cards up to what is left of today's
 * new-card limit. Suspended cards never appear.
 *
 * @param {object[]} cards   every card of one deck, as stored on the device
 * @param {number}   newLeft new cards still allowed today
 * @param {number}   now     epoch milliseconds
 */
export function selectStudyQueue(cards, newLeft, now = Date.now()) {
    const active = cards.filter((card) => !card.suspended);

    const reviews = active
        .filter((card) => card.state !== CardState.New && dueTime(card) <= now)
        .sort((a, b) => rank(a) - rank(b) || dueTime(a) - dueTime(b) || a.id - b.id)
        .slice(0, MAX_REVIEWS_PER_SESSION);

    const fresh = active
        .filter((card) => card.state === CardState.New)
        .sort((a, b) => a.id - b.id)
        .slice(0, Math.max(0, newLeft));

    return [...reviews, ...fresh];
}

/**
 * Whether `now` falls in the same study day as `since`.
 *
 * Study days roll over at the user's rollover hour in their timezone, the
 * scheduler's own day arithmetic, so it is delegated to Scheduler.dayDifference.
 */
export function sameStudyDay(scheduler, since, now) {
    if (!since) return false;

    return scheduler.dayDifference(new Date(since), new Date(now)) === 0;
}

/**
 * New cards still allowed today for a deck.
 *
 * `deck.startedToday` is the server's count at download time plus any new cards
 * started on this device since, anchored at `deck.dayStartedAt`. Once the study
 * day has rolled over, the count starts again from zero.
 */
export function newCardsLeft(scheduler, deck, now) {
    const limit = deck?.limit ?? 10;
    const started = sameStudyDay(scheduler, deck?.dayStartedAt, now) ? (deck.startedToday ?? 0) : 0;

    return Math.max(0, limit - started);
}

/** The deck record after one more new card was started at `now`. */
export function countNewCardStarted(scheduler, deck, now) {
    const sameDay = sameStudyDay(scheduler, deck?.dayStartedAt, now);

    return {
        ...deck,
        // Any instant inside the new study day anchors it.
        dayStartedAt: sameDay ? deck.dayStartedAt : new Date(now).toISOString(),
        startedToday: (sameDay ? (deck.startedToday ?? 0) : 0) + 1,
    };
}

/** "1m", "10m", "6h", "4d", "2.5mo", "1.2y": the label under an answer button. */
export function formatInterval(seconds) {
    if (!Number.isFinite(seconds) || seconds <= 0) return '';

    const minutes = seconds / 60;
    if (minutes < 60) return `${Math.max(1, Math.round(minutes))}m`;

    const hours = minutes / 60;
    if (hours < 24) return `${Math.round(hours)}h`;

    const days = hours / 24;
    if (days < 31) return `${Math.round(days)}d`;

    const trim = (value) => value.toFixed(1).replace(/\.0$/, '');
    if (days < 365) return `${trim(days / 30)}mo`;

    return `${trim(days / 365)}y`;
}
