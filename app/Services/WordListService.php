<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Card;
use App\Models\Deck;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Word lists: where looked-up words are saved.
 *
 * A list is an ordinary personal deck and a saved word an ordinary card, so a
 * list is studied, scheduled, exported and synced offline with no code of its
 * own, and every deck the user owns can be picked as a list.
 *
 * Words go into the list the user picked last. Before they pick one, and after
 * the one they picked is deleted, they go into the default list, which is
 * created the first time it is needed. Every query here goes through
 * $user->decks(), so one user's lists are never visible to another.
 */
class WordListService
{
    public const DEFAULT_LIST_NAME = 'My Words';

    /**
     * Key in users.preferences holding the id of the list picked last.
     */
    private const SELECTED_LIST = 'word_list_id';

    /**
     * The user's default list, created on first use.
     *
     * firstOrCreate inserts through createOrFirst, so two requests racing here
     * both end up with the single row that the partial unique index
     * decks_one_default_list_per_user allows.
     */
    public function defaultListFor(User $user): Deck
    {
        return $user->decks()->firstOrCreate(['is_default_list' => true], ['name' => self::DEFAULT_LIST_NAME]);
    }

    /**
     * The list new words go into: the one picked last, else the default list.
     */
    public function selectedListFor(User $user): Deck
    {
        $selected = $user->preference(self::SELECTED_LIST);

        return (is_int($selected) ? $user->decks()->find($selected) : null) ?? $this->defaultListFor($user);
    }

    /**
     * The same choice as selectedListFor(), as an id and without creating
     * anything, for rendering the list picker. Null means the default list,
     * which does not exist yet.
     */
    public function selectedListIdFor(User $user): ?int
    {
        $selected = $user->preference(self::SELECTED_LIST);

        if (is_int($selected) && $user->decks()->whereKey($selected)->exists()) {
            return $selected;
        }

        return $user->decks()->where('is_default_list', true)->value('id');
    }

    /**
     * Remembers the list new words go into; null goes back to the default
     * list. The caller authorizes: the list must be the user's own.
     */
    public function select(User $user, ?Deck $list): void
    {
        $user->setPreference(self::SELECTED_LIST, $list?->id);
    }

    public function createList(User $user, string $name): Deck
    {
        return $user->decks()->create(['name' => $name]);
    }

    /**
     * The card for this word already in the list, if there is one. Compared
     * without case, so "Ahead" is not saved again next to "ahead".
     */
    public function findWord(Deck $list, string $front): ?Card
    {
        return $list->cards()->whereRaw('lower(front) = ?', [mb_strtolower(trim($front))])->first();
    }

    /**
     * Saves a word as a new card. The pronunciation goes where the curriculum
     * seeders keep it, in `audio`.
     */
    public function addWord(Deck $list, string $front, string $back, ?string $description = null, ?string $pronunciation = null): Card
    {
        return $list->cards()->create([
            'front' => $front,
            'back' => $back,
            'description' => $description,
            'audio' => $pronunciation ? ['pronunciation' => $pronunciation] : null,
        ]);
    }

    /**
     * The list picker's lists: the default list first, then by name.
     *
     * @param  Collection<int, Deck>  $decks  the user's decks, loaded withCount('cards')
     * @return list<array{id: int, name: string, is_default_list: bool, cards_count: int}>
     */
    public function pickerLists(Collection $decks): array
    {
        return $decks
            ->sortBy([['is_default_list', 'desc'], fn (Deck $a, Deck $b): int => strcasecmp($a->name, $b->name)])
            ->map(fn (Deck $deck): array => $this->payload($deck))
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: int, name: string, is_default_list: bool, cards_count: int}>
     */
    public function pickerListsFor(User $user): array
    {
        return $this->pickerLists($user->decks()->withCount('cards')->get());
    }

    /**
     * How the list picker sees a list.
     *
     * @return array{id: int, name: string, is_default_list: bool, cards_count: int}
     */
    public function payload(Deck $list): array
    {
        return [
            'id' => $list->id,
            'name' => $list->name,
            'is_default_list' => $list->is_default_list,
            'cards_count' => $list->cards_count ?? $list->cards()->count(),
        ];
    }
}
