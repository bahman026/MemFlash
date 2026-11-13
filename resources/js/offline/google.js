/**
 * English ↔ Persian from Google Translate, asked by the browser.
 *
 * The server cannot translate from Iran: MyMemory never answers it, and Google
 * sends its address to a captcha page after a request or two (measured
 * 2026-11-13). The user's browser can still reach Google, so the lookup asks it
 * directly, in parallel with the server, and merges the answer.
 *
 * clients5.google.com is the endpoint of Google's own dictionary extension. It
 * allows any origin (Access-Control-Allow-Origin: *) and returns dictionary
 * entries as well as the translation. Unofficial: if it stops answering, the
 * lookup simply has one source fewer.
 */
import { clean, isPersian } from './dictionary.js';

const URL = 'https://clients5.google.com/translate_a/single';

const MAX_TRANSLATIONS = 6;

/** 'auto' resolved by script, as the server does; null for en-en (meanings only). */
export function resolveDirection(query, direction) {
    const resolved = direction === 'auto' ? (isPersian(query) ? 'fa-en' : 'en-fa') : direction;

    return resolved === 'en-fa' || resolved === 'fa-en' ? resolved : null;
}

/**
 * Google's answer in the shape GET /api/words/lookup returns, or null.
 *
 * data[0]: chunks [translation, original, …]; the one with no translation
 * holds the transliterations [null, null, target, source]. data[1]: dictionary
 * entries [part of speech, [terms…], …]. For a single word the dictionary's
 * first terms are better card answers than the sentence-style translation
 * ("run" → "اجرا کنید", where the dictionary says "دویدن"), so they come first.
 */
export function parseGoogle(data, query, direction) {
    if (!Array.isArray(data) || !Array.isArray(data[0])) return null;

    const chunks = data[0];
    const main = clean(chunks.filter((c) => Array.isArray(c) && typeof c[0] === 'string').map((c) => c[0]).join(''));
    const translit = chunks.find((c) => Array.isArray(c) && c[0] == null) ?? [];
    const entries = (Array.isArray(data[1]) ? data[1] : []).filter((e) => Array.isArray(e) && Array.isArray(e[1]));

    const firsts = entries.map((e) => e[1][0]);
    const rest = entries.flatMap((e) => e[1].slice(1));
    const key = (text) => text.toLowerCase();
    const seen = new Set([key(clean(query))]);
    const translations = [];

    for (const text of [...firsts, main, ...rest]) {
        const value = clean(text);
        if (!value || seen.has(key(value))) continue;
        seen.add(key(value));
        translations.push(value);
        if (translations.length === MAX_TRANSLATIONS) break;
    }

    if (translations.length === 0) return null;

    const [language, target] = direction.split('-');
    const sourceSound = typeof translit[3] === 'string' ? translit[3].trim() : '';
    const phonetic = language === 'en' && sourceSound ? `/${sourceSound}/` : null;
    const persianToEnglish = language === 'fa';

    return {
        query,
        direction,
        language,
        target,
        word: persianToEnglish ? translations[0] : query,
        phonetic,
        senses: [],
        translations,
        sources: ['Google Translate'],
        card: {
            front: persianToEnglish ? translations[0] : query,
            back: persianToEnglish ? query : translations[0],
            description: null,
            pronunciation: phonetic,
        },
        browser: true,
    };
}

/** Never throws: no answer (blocked, offline, rate-limited, en-en) is null. */
export async function translateInBrowser(input, direction = 'auto', { fetchImpl = globalThis.fetch, timeoutMs = 8000 } = {}) {
    const query = clean(input);
    const resolved = query ? resolveDirection(query, direction) : null;
    if (!resolved || typeof fetchImpl !== 'function') return null;

    const [sl, tl] = resolved.split('-');
    const params = new URLSearchParams({ client: 'dict-chrome-ex', sl, tl, q: query.slice(0, 100) });
    for (const dt of ['t', 'bd', 'rm']) params.append('dt', dt);

    const controller = typeof AbortController === 'function' ? new AbortController() : null;
    const timer = controller ? setTimeout(() => controller.abort(), timeoutMs) : null;

    try {
        const response = await fetchImpl(`${URL}?${params}`, { credentials: 'omit', signal: controller?.signal });
        if (!response.ok) return null;

        return parseGoogle(await response.json(), query, resolved);
    } catch {
        return null;
    } finally {
        if (timer) clearTimeout(timer);
    }
}
