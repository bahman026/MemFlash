import { test } from 'node:test';
import assert from 'node:assert/strict';

import { parseGoogle, resolveDirection, translateInBrowser } from './google.js';

// Trimmed from a real answer for "run", en → fa (2026-11-13).
const RUN = [
    [['اجرا کنید', 'run', null, null, 3], [null, null, 'ejra konid', 'rən']],
    [
        ['verb', ['دویدن', 'راندن', 'اداره کردن', 'پیمودن']],
        ['noun', ['اجرا', 'دویدن', 'دوره']],
    ],
    'en',
];

test('puts the dictionary\'s first answers before the sentence translation', () => {
    const result = parseGoogle(RUN, 'run', 'en-fa');

    assert.deepEqual(result.translations, ['دویدن', 'اجرا', 'اجرا کنید', 'راندن', 'اداره کردن', 'پیمودن']);
    assert.deepEqual(result.card, { front: 'run', back: 'دویدن', description: null, pronunciation: '/rən/' });
    assert.equal(result.phonetic, '/rən/');
    assert.equal(result.language, 'en');
    assert.equal(result.target, 'fa');
    assert.deepEqual(result.sources, ['Google Translate']);
});

test('puts English on the front for Persian, as the server does', () => {
    const result = parseGoogle([[['dagger', 'خنجر']], null, 'fa'], 'خنجر', 'fa-en');

    assert.deepEqual(result.translations, ['dagger']);
    assert.deepEqual(result.card, { front: 'dagger', back: 'خنجر', description: null, pronunciation: null });
});

test('drops an answer that only repeats the word, and anything unreadable', () => {
    assert.equal(parseGoogle([[['Xyzzy', 'xyzzy']], null, 'en'], 'xyzzy', 'en-fa'), null);
    assert.equal(parseGoogle('<html>Sorry…</html>', 'run', 'en-fa'), null);
    assert.equal(parseGoogle(null, 'run', 'en-fa'), null);
});

test('detects the direction, and leaves English → English to the server', () => {
    assert.equal(resolveDirection('run', 'auto'), 'en-fa');
    assert.equal(resolveDirection('خنجر', 'auto'), 'fa-en');
    assert.equal(resolveDirection('run', 'en-en'), null);
});

test('asks Google with the word and the direction, and never throws', async () => {
    let asked = null;
    const ok = async (url) => {
        asked = new URL(url);
        return { ok: true, json: async () => RUN };
    };

    const result = await translateInBrowser(' Run. ', 'auto', { fetchImpl: ok });
    assert.equal(asked.host, 'clients5.google.com');
    assert.equal(asked.searchParams.get('q'), 'Run');
    assert.equal(asked.searchParams.get('sl'), 'en');
    assert.equal(asked.searchParams.get('tl'), 'fa');
    assert.deepEqual(asked.searchParams.getAll('dt'), ['t', 'bd', 'rm']);
    assert.equal(result.card.back, 'دویدن');

    assert.equal(await translateInBrowser('run', 'en-fa', { fetchImpl: async () => { throw new TypeError('blocked'); } }), null);
    assert.equal(await translateInBrowser('run', 'en-fa', { fetchImpl: async () => ({ ok: false, status: 429 }) }), null);
    assert.equal(await translateInBrowser('run', 'en-en', { fetchImpl: ok }), null);
});
