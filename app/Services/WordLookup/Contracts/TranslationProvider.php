<?php

declare(strict_types=1);

namespace App\Services\WordLookup\Contracts;

use App\Services\WordLookup\Language;
use App\Services\WordLookup\Translation;

/**
 * Translates a word between English and Persian, in either direction.
 *
 * To add a source: implement this, register it under a name in
 * config/services.php (word_lookup.providers), and list that name in
 * WORD_LOOKUP_TRANSLATORS. Providers are asked in that order until one answers.
 */
interface TranslationProvider
{
    /**
     * Null when the source has no translation, is not configured (a missing API
     * key), or could not be reached. Throwing is allowed too: WordLookupService
     * logs it and asks the next one.
     */
    public function translate(string $text, Language $from, Language $to): ?Translation;
}
