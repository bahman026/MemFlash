<?php

declare(strict_types=1);

namespace App\Services\WordLookup;

/**
 * What a DefinitionProvider found for one English word.
 */
final readonly class Definition
{
    /**
     * @param  list<Sense>  $senses  most common meaning first
     */
    public function __construct(
        public string $source,
        public array $senses,
        public ?string $phonetic = null,
    ) {}
}
