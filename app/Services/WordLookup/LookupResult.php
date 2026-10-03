<?php

declare(strict_types=1);

namespace App\Services\WordLookup;

/**
 * The answer to one lookup.
 *
 * en-fa: English meanings, plus Persian translations for the card's back.
 * fa-en: English equivalents only -- a word is enough, no definition.
 * en-en: English meanings only; the meaning is the card's back.
 */
final readonly class LookupResult
{
    /**
     * @param  list<Sense>  $senses  English meanings; empty for Persian input
     * @param  list<string>  $translations  into the target language, best first; empty for en-en
     * @param  list<string>  $sources  the providers that answered
     */
    public function __construct(
        public string $query,
        public Direction $direction,
        public array $senses = [],
        public array $translations = [],
        public ?string $phonetic = null,
        public array $sources = [],
    ) {}

    /**
     * The English word this lookup is about: what was typed, or for Persian
     * input its best English equivalent.
     */
    public function word(): ?string
    {
        return $this->direction->source() === Language::English ? $this->query : ($this->translations[0] ?? null);
    }

    /**
     * Whether every provider that should have answered did, so the result is
     * worth caching for long. A missing part may be a passing outage or an
     * exhausted daily quota, not a word the services do not know.
     */
    public function isComplete(): bool
    {
        return match ($this->direction) {
            Direction::EnglishToPersian => $this->senses !== [] && $this->translations !== [],
            Direction::PersianToEnglish => $this->translations !== [],
            Direction::EnglishToEnglish => $this->senses !== [],
        };
    }

    /**
     * What picking $sense puts on the card: `back` when the meaning is the
     * answer (null when the answer is a translation instead), and the `note`.
     *
     * Decided here for every sense rather than in the browser, so that picking
     * a meaning on screen and the card the server suggests cannot disagree.
     *
     * @return array{back: string|null, note: string|null}
     */
    public function senseCard(Sense $sense): array
    {
        return match (true) {
            // A dictionary card: the meaning is the answer, the example the hint.
            $this->direction === Direction::EnglishToEnglish => [
                'back' => $sense->definition,
                'note' => $sense->example !== null ? "Example: {$sense->example}" : null,
            ],
            // No Persian found: the English meaning is still a usable answer,
            // rather than leaving the card unsaveable.
            $this->translations === [] => ['back' => $sense->definition, 'note' => $sense->note()],
            default => ['back' => null, 'note' => $sense->note()],
        };
    }

    /**
     * The card the lookup suggests. English always goes on the front, whichever
     * language was typed, matching the curriculum decks.
     *
     * @return array{front: string, back: string, description: string|null, pronunciation: string|null}
     */
    public function card(): array
    {
        if ($this->direction === Direction::PersianToEnglish) {
            return ['front' => $this->translations[0] ?? '', 'back' => $this->query, 'description' => null, 'pronunciation' => null];
        }

        $picked = isset($this->senses[0]) ? $this->senseCard($this->senses[0]) : ['back' => null, 'note' => null];

        return [
            'front' => $this->query,
            'back' => $picked['back'] ?? $this->translations[0] ?? '',
            'description' => $picked['note'],
            'pronunciation' => $this->phonetic,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'query' => $this->query,
            'direction' => $this->direction->value,
            'language' => $this->direction->source()->value,
            'target' => $this->direction->target()->value,
            'word' => $this->word(),
            'phonetic' => $this->phonetic,
            'senses' => array_map(fn (Sense $sense): array => $sense->toArray() + $this->senseCard($sense), $this->senses),
            'translations' => $this->translations,
            'sources' => $this->sources,
            'card' => $this->card(),
        ];
    }

    /**
     * Rebuilds a result from toArray(), which is what the cache holds: plain
     * arrays survive a class being renamed, serialized objects do not.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            query: $data['query'],
            direction: Direction::from($data['direction']),
            senses: array_values(array_map(Sense::fromArray(...), $data['senses'] ?? [])),
            translations: array_values($data['translations'] ?? []),
            phonetic: $data['phonetic'] ?? null,
            sources: array_values($data['sources'] ?? []),
        );
    }
}
