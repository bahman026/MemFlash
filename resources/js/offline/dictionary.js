/**
 * Instant lookups from the words already on the device.
 *
 * The server's lookup asks outside services (Wiktionary, a translator), which on
 * a slow or filtered connection can take half a minute. Every curriculum word and
 * every card the user owns is downloaded once (GET /api/words/dictionary), and a
 * lookup that matches one of them is answered from here at once. The server's
 * answer still follows, adding meanings; this only covers translations.
 *
 * Pure: no IndexedDB, no fetch, no DOM. sync.js stores the rows and calls these.
 */

const PERSIAN_SCRIPT = /\p{Script=Arabic}/u;

// The forms Text::clean() normalizes on the server: Arabic yeh and kaf.
const PERSIAN_LETTERS = { 'ي': 'ی', 'ى': 'ی', 'ك': 'ک' };

// Several meanings in one back: "پیروز شدن، بردن", "big; large", "a/b".
const SEPARATORS = /\s*[،,؛;/]\s*/u;

const MAX_TRANSLATIONS = 6;

export const isPersian = (text) => PERSIAN_SCRIPT.test(text);

/** Display form: the same cleaning the server applies to a query. */
export function clean(text) {
    return String(text ?? '')
        .replace(/[يىك]/gu, (c) => PERSIAN_LETTERS[c])
        .replace(/[\s ]+/gu, ' ')
        .replace(/^[\s"'«»“”‘’]+|[\s.,;:!?،؛؟…"'«»“”‘’]+$/gu, '');
}

/**
 * Matching form. English: lower case, without a leading "to", "a", "an" or "the",
 * so "To Win" finds "win". Persian: no diacritics, and no spaces or zero-width
 * non-joiners, which people type interchangeably ("هیجان‌زده" / "هیجان زده").
 */
export function matchKey(text) {
    const value = clean(text);

    if (isPersian(value)) {
        return value.replace(/[ً-ٰٟ]/gu, '').replace(/[\s‌‏]+/gu, '');
    }

    return value.toLowerCase().replace(/^(?:to|a|an|the)\s+/u, '');
}

function add(map, key, value) {
    if (!key) return;
    const list = map.get(key);
    if (list) list.push(value);
    else map.set(key, [value]);
}

/**
 * Index the downloaded rows, [front, back, pronunciation, mine], both ways:
 * English front → entries, and each Persian meaning of a back → entries.
 */
export function buildIndex(entries) {
    const english = new Map();
    const persian = new Map();

    for (const row of entries ?? []) {
        const [front, back, pronunciation = null, mine = 0] = row;
        if (!front || !back) continue;

        const entry = { front: clean(front), back: clean(back), pronunciation: pronunciation || null, mine: Boolean(mine) };
        add(english, matchKey(front), entry);

        for (const meaning of back.split(SEPARATORS)) {
            add(persian, matchKey(meaning), entry);
        }
    }

    return { english, persian, size: entries?.length ?? 0 };
}

const unique = (values) => [...new Set(values.filter(Boolean))];

/** The user's own cards first: they are the answers this user chose. */
const byOwnFirst = (entries) => [...entries].sort((a, b) => Number(b.mine) - Number(a.mine));

/**
 * A lookup answered from the device, in the shape GET /api/words/lookup returns,
 * or null when the word is not on the device.
 *
 * `direction` is the lookup panel's: 'auto', 'en-fa', 'fa-en' or 'en-en'. The
 * English dictionary (en-en) is meanings only, which the device does not have.
 */
export function lookUpLocally(index, input, direction = 'auto') {
    const query = clean(input);
    if (!index || !query) return null;

    const resolved = direction === 'auto' ? (isPersian(query) ? 'fa-en' : 'en-fa') : direction;
    if (resolved === 'en-en') return null;

    if (resolved === 'en-fa') {
        const found = byOwnFirst(index.english.get(matchKey(query)) ?? []);
        if (found.length === 0) return null;

        const translations = unique(found.flatMap((e) => e.back.split(SEPARATORS).map(clean))).slice(0, MAX_TRANSLATIONS);
        const pronunciation = found.find((e) => e.pronunciation)?.pronunciation ?? null;

        return result(query, 'en-fa', translations, pronunciation, {
            front: query,
            back: translations[0],
            description: null,
            pronunciation,
        });
    }

    const found = byOwnFirst(index.persian.get(matchKey(query)) ?? []);
    if (found.length === 0) return null;

    const translations = unique(found.map((e) => e.front)).slice(0, MAX_TRANSLATIONS);

    // As on the server: English always goes on the front.
    return result(query, 'fa-en', translations, null, {
        front: translations[0],
        back: query,
        description: null,
        pronunciation: null,
    });
}

function result(query, direction, translations, phonetic, card) {
    const [language, target] = direction.split('-');

    return {
        query,
        direction,
        language,
        target,
        word: language === 'en' ? query : translations[0],
        phonetic,
        senses: [],
        translations,
        sources: ['MemFlash'],
        card,
        local: true,
    };
}
