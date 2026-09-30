import { test } from 'node:test';
import assert from 'node:assert/strict';

import { Scheduler, CardState } from '../fsrs/fsrs.js';
import {
    MAX_REVIEWS_PER_SESSION,
    countNewCardStarted,
    formatInterval,
    newCardsLeft,
    sameStudyDay,
    selectStudyQueue,
} from './queue.js';

const now = Date.parse('2026-09-30T12:00:00Z');
const ago = (minutes) => new Date(now - minutes * 60_000).toISOString();
const ahead = (minutes) => new Date(now + minutes * 60_000).toISOString();
const card = (id, state, due = null, extra = {}) => ({ id, state, due, ...extra });
const utc = new Scheduler({ rolloverHour: 4, timezone: 'UTC' });

test('orders learning first, then reviews by due date, then new cards', () => {
    const queue = selectStudyQueue(
        [
            card(1, CardState.New),
            card(2, CardState.Review, ago(10)),
            card(3, CardState.Learning, ago(1)),
            card(4, CardState.Review, ago(60)),
            card(5, CardState.New),
        ],
        10,
        now
    );

    assert.deepEqual(queue.map((c) => c.id), [3, 4, 2, 1, 5]);
});

test('leaves out cards not yet due and suspended cards', () => {
    const queue = selectStudyQueue(
        [
            card(1, CardState.Review, ahead(60)),
            card(2, CardState.Learning, ahead(5)),
            card(3, CardState.Review, ago(5), { suspended: true }),
            card(4, CardState.New, null, { suspended: true }),
            card(5, CardState.Review, ago(5)),
        ],
        10,
        now
    );

    assert.deepEqual(queue.map((c) => c.id), [5]);
});

test('caps new cards at what is left of the daily limit, but never the reviews', () => {
    const cards = [
        ...Array.from({ length: 5 }, (_, i) => card(i + 1, CardState.Review, ago(i + 1))),
        ...Array.from({ length: 10 }, (_, i) => card(100 + i, CardState.New)),
    ];

    const queue = selectStudyQueue(cards, 2, now);

    assert.equal(queue.filter((c) => c.state === CardState.Review).length, 5);
    assert.deepEqual(queue.filter((c) => c.state === CardState.New).map((c) => c.id), [100, 101]);
    assert.equal(selectStudyQueue(cards, 0, now).length, 5);
});

test('loads at most one session of reviews', () => {
    const cards = Array.from({ length: MAX_REVIEWS_PER_SESSION + 20 }, (_, i) => card(i + 1, CardState.Review, ago(1)));

    assert.equal(selectStudyQueue(cards, 0, now).length, MAX_REVIEWS_PER_SESSION);
});

test('counts the study day from the rollover hour, not midnight', () => {
    // Rollover at 4am UTC: 03:00 still belongs to the previous day.
    assert.equal(sameStudyDay(utc, '2026-09-30T04:00:00Z', Date.parse('2026-09-30T23:59:00Z')), true);
    assert.equal(sameStudyDay(utc, '2026-09-30T04:00:00Z', Date.parse('2026-10-01T03:00:00Z')), true);
    assert.equal(sameStudyDay(utc, '2026-09-30T04:00:00Z', Date.parse('2026-10-01T04:00:00Z')), false);
    assert.equal(sameStudyDay(utc, null, now), false);
});

test('keeps counting new cards started offline against the daily limit', () => {
    let deck = { limit: 3, startedToday: 1, dayStartedAt: '2026-09-30T04:00:00Z' };

    assert.equal(newCardsLeft(utc, deck, now), 2);

    deck = countNewCardStarted(utc, deck, now);
    deck = countNewCardStarted(utc, deck, now);

    assert.equal(deck.startedToday, 3);
    assert.equal(newCardsLeft(utc, deck, now), 0);
});

test('starts the count again once the study day rolls over', () => {
    const yesterday = { limit: 3, startedToday: 3, dayStartedAt: '2026-09-29T04:00:00Z' };

    assert.equal(newCardsLeft(utc, yesterday, now), 3);

    const today = countNewCardStarted(utc, yesterday, now);
    assert.equal(today.startedToday, 1);
    assert.equal(newCardsLeft(utc, today, now), 2);
});

test('uses the default limit when the deck record is missing', () => {
    assert.equal(newCardsLeft(utc, undefined, now), 10);
});

test('labels intervals the way the answer buttons show them', () => {
    assert.equal(formatInterval(60), '1m');
    assert.equal(formatInterval(330), '6m');
    assert.equal(formatInterval(600), '10m');
    assert.equal(formatInterval(3 * 3600), '3h');
    assert.equal(formatInterval(86400), '1d');
    assert.equal(formatInterval(23 * 86400), '23d');
    assert.equal(formatInterval(51 * 86400), '1.7mo');
    assert.equal(formatInterval(90 * 86400), '3mo');
    assert.equal(formatInterval(400 * 86400), '1.1y');
    assert.equal(formatInterval(0), '');
    assert.equal(formatInterval(undefined), '');
});
