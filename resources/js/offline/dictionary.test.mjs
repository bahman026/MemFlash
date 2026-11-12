import { test } from 'node:test';
import assert from 'node:assert/strict';

import { buildIndex, lookUpLocally, matchKey } from './dictionary.js';

const index = buildIndex([
    ['win', 'پیروز شدن، بردن', '/wɪn/', 0],
    ['excited', 'هیجان‌زده', '/ɪkˈsaɪtɪd/', 0],
    ['spare time', 'اوقات فراغت', null, 0],
    ['beat', 'شکست دادن', '/biːt/', 0],
    ['defeat', 'شكست دادن', null, 0], // Arabic kaf, as some sources write it
    ['win', 'برنده شدن', null, 1], // the user's own card
]);

test('answers an English word with its Persian meanings, the user\'s own first', () => {
    const result = lookUpLocally(index, 'win', 'en-fa');

    assert.deepEqual(result.translations, ['برنده شدن', 'پیروز شدن', 'بردن']);
    assert.equal(result.phonetic, '/wɪn/');
    assert.deepEqual(result.card, { front: 'win', back: 'برنده شدن', description: null, pronunciation: '/wɪn/' });
    assert.equal(result.language, 'en');
    assert.equal(result.target, 'fa');
    assert.deepEqual(result.senses, []);
    assert.equal(result.local, true);
});

test('detects the direction from the script, as the server does', () => {
    assert.equal(lookUpLocally(index, 'Spare Time.', 'auto').card.back, 'اوقات فراغت');
    assert.equal(lookUpLocally(index, 'اوقات فراغت', 'auto').direction, 'fa-en');
});

test('matches English without "to" or an article, and keeps what was typed', () => {
    const result = lookUpLocally(index, 'To Win', 'auto');

    assert.equal(result.query, 'To Win');
    assert.equal(result.card.front, 'To Win');
    assert.equal(result.card.back, 'برنده شدن');
});

test('answers Persian with English, putting English on the front', () => {
    const result = lookUpLocally(index, 'بردن', 'fa-en');

    assert.deepEqual(result.translations, ['win']);
    assert.deepEqual(result.card, { front: 'win', back: 'بردن', description: null, pronunciation: null });
});

test('matches Persian across zero-width non-joiners, spaces and Arabic letters', () => {
    assert.deepEqual(lookUpLocally(index, 'هیجان زده', 'fa-en').translations, ['excited']);
    assert.deepEqual(lookUpLocally(index, 'شكست دادن', 'fa-en').translations, ['beat', 'defeat']);
    assert.equal(matchKey('هیجان‌زده'), matchKey('هيجان زده'));
});

test('leaves unknown words and the English dictionary to the server', () => {
    assert.equal(lookUpLocally(index, 'serendipity', 'en-fa'), null);
    assert.equal(lookUpLocally(index, 'win', 'en-en'), null);
    assert.equal(lookUpLocally(index, '', 'auto'), null);
    assert.equal(lookUpLocally(null, 'win', 'auto'), null);
});
