<?php

declare(strict_types=1);

namespace App\Services\WordLookup\Contracts;

use App\Services\WordLookup\Definition;

/**
 * Explains an English word in English.
 *
 * To add a source: implement this, register it under a name in
 * config/services.php (word_lookup.providers), and list that name in
 * WORD_LOOKUP_DEFINITIONS. Providers are asked in that order until one answers.
 */
interface DefinitionProvider
{
    /**
     * Null when the source has no entry for the word, or could not be reached.
     * Throwing is allowed too: WordLookupService logs it and asks the next one.
     */
    public function define(string $word): ?Definition;
}
