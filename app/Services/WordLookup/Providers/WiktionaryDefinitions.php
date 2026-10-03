<?php

declare(strict_types=1);

namespace App\Services\WordLookup\Providers;

use App\Services\WordLookup\Contracts\DefinitionProvider;
use App\Services\WordLookup\Definition;
use App\Services\WordLookup\Sense;
use App\Services\WordLookup\Text;

/**
 * English definitions from Wiktionary's REST API. Free and keyless, covers
 * phrases ("look forward to") as well as words, and has example sentences.
 * No pronunciation.
 *
 * @see https://en.wiktionary.org/api/rest_v1/#/Page%20content/get_page_definition__term_
 */
final class WiktionaryDefinitions implements DefinitionProvider
{
    use CallsLookupService;

    private const URL = 'https://en.wiktionary.org/api/rest_v1/page/definition/';

    /**
     * Some "examples" are quotations from literature, a paragraph long. Past
     * this they stop helping on a flashcard.
     */
    private const MAX_EXAMPLE_LENGTH = 200;

    public function __construct(
        private readonly int $timeout = 5,
    ) {}

    public function define(string $word): ?Definition
    {
        // Titles are case-sensitive ("Turkey" the country, "turkey" the bird),
        // so the word as typed first, then in lower case.
        $entries = $this->entries($word);

        if ($entries === null && mb_strtolower($word) !== $word) {
            $entries = $this->entries(mb_strtolower($word));
        }

        $senses = [];

        foreach ($entries ?? [] as $entry) {
            $partOfSpeech = is_string($entry['partOfSpeech'] ?? null) ? mb_strtolower(Text::fromHtml($entry['partOfSpeech'])) : null;

            foreach ($entry['definitions'] ?? [] as $definition) {
                $text = Text::fromHtml((string) ($definition['definition'] ?? ''));

                // An empty definition is a heading for nested senses.
                if ($text === '') {
                    continue;
                }

                $senses[] = new Sense($text, $partOfSpeech ?: null, $this->example($definition['examples'] ?? []));
            }
        }

        return $senses === [] ? null : new Definition('wiktionary', $senses);
    }

    /**
     * The English section of the page, or null when there is no such page.
     *
     * @return array<int, array<string, mixed>>|null
     */
    private function entries(string $word): ?array
    {
        $response = $this->http()->get(self::URL . rawurlencode(str_replace(' ', '_', $word)));

        $entries = $response->successful() ? $response->json('en') : null;

        return is_array($entries) && $entries !== [] ? $entries : null;
    }

    /**
     * @param  mixed  $examples  a list of HTML strings, as the API sends them
     */
    private function example(mixed $examples): ?string
    {
        if (! is_array($examples) || ! is_string($examples[0] ?? null)) {
            return null;
        }

        $example = Text::fromHtml($examples[0]);

        return $example !== '' && mb_strlen($example) <= self::MAX_EXAMPLE_LENGTH ? $example : null;
    }
}
