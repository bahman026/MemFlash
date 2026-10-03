<?php

declare(strict_types=1);

namespace App\Services\WordLookup\Providers;

use App\Services\WordLookup\Contracts\TranslationProvider;
use App\Services\WordLookup\Language;
use App\Services\WordLookup\Text;
use App\Services\WordLookup\Translation;

/**
 * Translations from MyMemory, a translation memory with machine translation
 * behind it. Keyless, which makes it the default, but the anonymous quota is
 * 5,000 characters a day per server IP. MYMEMORY_EMAIL raises it to 50,000.
 *
 * Besides its best answer it returns the human translations it matched, which
 * become the alternatives the user can pick from.
 *
 * @see https://mymemory.translated.net/doc/spec.php
 */
final class MyMemoryTranslator implements TranslationProvider
{
    use CallsLookupService;

    private const URL = 'https://api.mymemory.translated.net/get';

    /**
     * Below this a "match" is a fuzzy hit on some other sentence ("annoy" →
     * "it's ticklish"), not a translation of the word.
     */
    private const MIN_MATCH = 0.8;

    private const MAX_TRANSLATIONS = 6;

    public function __construct(
        private readonly int $timeout = 5,
        private readonly ?string $email = null,
    ) {}

    public function translate(string $text, Language $from, Language $to): ?Translation
    {
        $response = $this->http()->get(self::URL, array_filter([
            'q' => $text,
            'langpair' => "{$from->value}|{$to->value}",
            'de' => $this->email,
        ]));

        // A spent quota still answers HTTP 200: the status is in the body (429),
        // with a warning in English where the translation should be.
        if (! $response->successful() || (int) $response->json('responseStatus') !== 200) {
            return null;
        }

        $candidates = [$response->json('responseData.translatedText')];

        foreach ((array) $response->json('matches', []) as $match) {
            if (is_array($match) && (float) ($match['match'] ?? 0) >= self::MIN_MATCH) {
                $candidates[] = $match['translation'] ?? null;
            }
        }

        $texts = Text::translations($candidates, $to, self::MAX_TRANSLATIONS);

        return $texts === [] ? null : new Translation('mymemory', $texts);
    }
}
