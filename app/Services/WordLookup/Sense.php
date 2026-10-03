<?php

declare(strict_types=1);

namespace App\Services\WordLookup;

/**
 * One meaning of an English word: "adverb: At or towards the front."
 */
final readonly class Sense
{
    public function __construct(
        public string $definition,
        public ?string $partOfSpeech = null,
        public ?string $example = null,
    ) {}

    /**
     * The sense as one line of plain text, for a card's description under a
     * translated answer. (For an English-to-English card the meaning is the
     * answer itself; see LookupResult::senseCard().)
     */
    public function note(): string
    {
        $note = $this->partOfSpeech ? "{$this->partOfSpeech}: {$this->definition}" : $this->definition;

        return $this->example ? "{$note} Example: {$this->example}" : $note;
    }

    /**
     * @return array{definition: string, part_of_speech: string|null, example: string|null}
     */
    public function toArray(): array
    {
        return [
            'definition' => $this->definition,
            'part_of_speech' => $this->partOfSpeech,
            'example' => $this->example,
        ];
    }

    /**
     * @param  array{definition: string, part_of_speech?: string|null, example?: string|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data['definition'], $data['part_of_speech'] ?? null, $data['example'] ?? null);
    }
}
