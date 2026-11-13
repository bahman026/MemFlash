<?php

declare(strict_types=1);

namespace App\Services\WordLookup\Providers;

use App\Services\WordLookup\Contracts\TranslationProvider;
use App\Services\WordLookup\Language;
use App\Services\WordLookup\Text;
use App\Services\WordLookup\Translation;
use App\Services\WordLookup\VocabBank;

/**
 * Translations from the word bank on the server (VocabBank): no outside
 * service, so it answers from Iran, at once. Listed first; a word the bank
 * does not know goes on to the next translator.
 */
final class VocabBankTranslator implements TranslationProvider
{
    private const MAX_TRANSLATIONS = 6;

    // No timeout: WordLookupService passes one to every provider, and the
    // container drops an argument the constructor does not take.
    public function __construct(private readonly VocabBank $bank) {}

    public function translate(string $text, Language $from, Language $to): ?Translation
    {
        $texts = Text::translations($this->bank->translate($text, $from, self::MAX_TRANSLATIONS), $to, self::MAX_TRANSLATIONS);

        return $texts === [] ? null : new Translation('wiktionary', $texts);
    }
}
