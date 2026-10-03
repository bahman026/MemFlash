<?php

declare(strict_types=1);

namespace App\Services\WordLookup;

/**
 * What a TranslationProvider found for one word or phrase.
 */
final readonly class Translation
{
    /**
     * @param  list<string>  $texts  best first, cleaned and without duplicates
     */
    public function __construct(
        public string $source,
        public array $texts,
    ) {}
}
