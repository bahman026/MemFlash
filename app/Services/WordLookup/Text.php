<?php

declare(strict_types=1);

namespace App\Services\WordLookup;

/**
 * Cleans what users type and what lookup services return, so that the same
 * word compares, caches and saves the same way whichever path it came by.
 */
final class Text
{
    /**
     * Arabic code points that Persian writes differently. Services and keyboards
     * mix them freely ("اذيت" next to "اذیت"); without this the two spellings
     * would be offered as separate translations and cached as separate words.
     */
    private const PERSIAN_LETTERS = [
        "\u{064A}" => "\u{06CC}", // Arabic yeh → Persian yeh
        "\u{0649}" => "\u{06CC}", // alef maksura → Persian yeh
        "\u{0643}" => "\u{06A9}", // Arabic kaf → Persian kaf
    ];

    /**
     * Trimmed, single-spaced, without trailing sentence punctuation, with
     * Persian letters normalized. ZWNJ (U+200C) is kept: it is part of how
     * Persian words are spelled, not whitespace.
     */
    public static function clean(string $text): string
    {
        $text = strtr($text, self::PERSIAN_LETTERS);
        $text = preg_replace('/[\s\x{00A0}]+/u', ' ', $text) ?? $text;

        // Translation memories return whole segments, so "ahead" comes back as
        // "به جلو." -- the full stop belongs to someone else's sentence.
        //
        // A /u regex rather than trim(): trim() strips bytes, and the bytes of
        // a curly quote also occur inside Persian letters.
        return preg_replace('/^[\s"\'«»“”‘’]+|[\s.,;:!?،؛؟…"\'«»“”‘’]+$/u', '', $text) ?? $text;
    }

    /**
     * clean(), plus sentence case undone for English: "To win" is "to win".
     * Left alone when the second letter is also a capital, so "TV" stays "TV".
     */
    public static function cleanTranslation(string $text, Language $language): string
    {
        $text = self::clean(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if ($language === Language::English && preg_match('/^\p{Lu}(?:\p{Ll}|\s|$)/u', $text) === 1 && mb_strlen($text) > 1) {
            $text = mb_strtolower(mb_substr($text, 0, 1)) . mb_substr($text, 1);
        }

        return $text;
    }

    /**
     * A translation service's candidates, cleaned, in the target language only,
     * without duplicates, best first.
     *
     * Checking the script drops a service echoing the input back untranslated
     * ("ahead" → "ahead") and its quota warnings, which arrive in English in
     * place of a Persian answer.
     *
     * @param  iterable<mixed>  $candidates  best first
     * @return list<string>
     */
    public static function translations(iterable $candidates, Language $to, int $limit): array
    {
        $texts = [];

        foreach ($candidates as $candidate) {
            if (! is_string($candidate)) {
                continue;
            }

            $text = self::cleanTranslation($candidate, $to);
            $key = mb_strtolower($text);

            if ($text === '' || isset($texts[$key]) || Language::detect($text) !== $to) {
                continue;
            }

            $texts[$key] = $text;

            if (count($texts) >= $limit) {
                break;
            }
        }

        return array_values($texts);
    }

    /**
     * Plain text from a service's HTML fragment (Wiktionary marks up links and
     * labels inside definitions).
     */
    public static function fromHtml(string $html): string
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // &nbsp; decodes to U+00A0, which \s alone does not reliably match.
        return trim(preg_replace('/[\s\x{00A0}]+/u', ' ', $text) ?? $text);
    }
}
