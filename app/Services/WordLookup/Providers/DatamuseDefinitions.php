<?php

declare(strict_types=1);

namespace App\Services\WordLookup\Providers;

use App\Services\WordLookup\Contracts\DefinitionProvider;
use App\Services\WordLookup\Definition;
use App\Services\WordLookup\Sense;
use App\Services\WordLookup\Text;

/**
 * English definitions from Datamuse, which serves WordNet's glosses. Free and
 * keyless, with an IPA pronunciation; no example sentences.
 *
 * @see https://www.datamuse.com/api/
 */
final class DatamuseDefinitions implements DefinitionProvider
{
    use CallsLookupService;

    private const URL = 'https://api.datamuse.com/words';

    private const PARTS_OF_SPEECH = [
        'n' => 'noun',
        'v' => 'verb',
        'adj' => 'adjective',
        'adv' => 'adverb',
    ];

    public function __construct(
        private readonly int $timeout = 5,
    ) {}

    public function define(string $word): ?Definition
    {
        $response = $this->http()->get(self::URL, ['sp' => $word, 'md' => 'dr', 'ipa' => 1, 'max' => 1]);

        $entry = $response->successful() ? $response->json('0') : null;

        // sp= is a spelling pattern, so check the match is the word itself and
        // not whatever "ahea*" happened to find.
        if (! is_array($entry) || mb_strtolower((string) ($entry['word'] ?? '')) !== mb_strtolower($word)) {
            return null;
        }

        $senses = [];

        // Each one is "adv\tAt or towards the front."
        foreach ($entry['defs'] ?? [] as $def) {
            [$partOfSpeech, $text] = array_pad(explode("\t", (string) $def, 2), 2, '');
            $text = Text::fromHtml($text);

            if ($text !== '') {
                $senses[] = new Sense($text, self::PARTS_OF_SPEECH[$partOfSpeech] ?? null);
            }
        }

        if ($senses === []) {
            return null;
        }

        return new Definition('datamuse', $senses, $this->phonetic($entry['tags'] ?? []));
    }

    /**
     * @param  mixed  $tags  e.g. ["adv", "ipa_pron:ʌhˈɛd"]
     */
    private function phonetic(mixed $tags): ?string
    {
        foreach (is_array($tags) ? $tags : [] as $tag) {
            if (is_string($tag) && str_starts_with($tag, 'ipa_pron:')) {
                $ipa = trim(substr($tag, strlen('ipa_pron:')));

                return $ipa !== '' ? "/{$ipa}/" : null;
            }
        }

        return null;
    }
}
