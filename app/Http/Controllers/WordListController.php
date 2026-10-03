<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Constants\DeckLimits;
use App\Models\Deck;
use App\Services\WordListService;
use App\Services\WordLookup\Direction;
use App\Services\WordLookup\WordLookupService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * The dashboard's word lookup and the lists it saves words into.
 *
 * JSON endpoints behind session auth and CSRF, like the study API. A list is a
 * deck (see WordListService), so ownership is DeckPolicy's `update`: the same
 * check as adding a card by hand, and it fails for other people's public decks.
 */
class WordListController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly WordListService $lists,
        private readonly WordLookupService $lookup,
    ) {}

    /**
     * GET /lookup?q=later&sl=en&tl=fa
     *
     * The lookup on a page of its own, opened on a word. It is the MemFlash
     * counterpart of translate.google.com/?sl=en&tl=fa&text=%%SS, and takes the
     * same parameters: another app (a subtitle reader, a browser search
     * shortcut) can send a word straight in, in a chosen direction --
     * sl=en&tl=fa, sl=fa&tl=en, or sl=en&tl=en for the meaning in English.
     * Without sl/tl the direction is detected. The page looks the word up
     * itself, through lookup().
     */
    public function page(Request $request): View
    {
        $user = $request->user();
        // text= as on Google Translate, so its URL works with the host changed.
        $query = $request->query('q', $request->query('text'));
        $direction = Direction::fromQuery($request->query('sl'), $request->query('tl'));

        return view('pages.lookup', [
            // Not validated: a bad q should still open the page, just empty.
            'query' => is_string($query) ? mb_substr(trim($query), 0, 100) : '',
            'direction' => $direction->value ?? 'auto',
            'lists' => $this->lists->pickerListsFor($user),
            'selectedListId' => $this->lists->selectedListIdFor($user),
        ]);
    }

    /**
     * GET /api/words/lookup?q=ahead[&sl=en|fa|auto][&tl=fa|en] (see Direction::fromQuery())
     *
     * Always answers 200 with whatever was found, possibly nothing: the word
     * can still be saved by typing its meaning in, so a lookup that comes back
     * empty must not stand in the way of the list.
     */
    public function lookup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'required|string|max:100',
            'sl' => 'nullable|in:auto,en,fa',
            'tl' => 'nullable|in:en,fa',
        ]);

        $result = $this->lookup->lookup(
            $validated['q'],
            Direction::fromQuery($validated['sl'] ?? null, $validated['tl'] ?? null),
        );

        return response()->json($result->toArray());
    }

    /**
     * POST /api/word-lists/words
     *
     * list_id is a list's id, or "default" for the default list (created if
     * need be). Without one the word goes into the list picked last.
     *
     * "default" has to be explicit: the picker remembers a choice in a
     * separate request, and a word saved right after picking the default list
     * could otherwise overtake it and land in the list picked before.
     *
     * Saving into a list also picks it, so the next word follows.
     */
    public function addWord(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'front' => 'required|string|max:1000',
            'back' => 'required|string|max:1000',
            'description' => 'nullable|string|max:2000',
            'pronunciation' => 'nullable|string|max:100',
            'list_id' => ['nullable', 'regex:/^(default|[1-9][0-9]*)$/'],
        ]);

        $user = $request->user();
        $list = match ($validated['list_id'] ?? null) {
            null => $this->lists->selectedListFor($user),
            'default' => $this->lists->defaultListFor($user),
            default => Deck::query()->findOrFail((int) $validated['list_id']),
        };

        $this->authorize('update', $list);

        $this->lists->select($user, $list);

        $existing = $this->lists->findWord($list, $validated['front']);

        if ($existing !== null) {
            return response()->json([
                'created' => false,
                'card' => ['id' => $existing->id, 'front' => $existing->front, 'back' => $existing->back],
                'list' => $this->lists->payload($list),
                'message' => "\"{$existing->front}\" is already in {$list->name}.",
            ]);
        }

        if ($list->hasReachedCardLimit()) {
            throw ValidationException::withMessages([
                'list_id' => "{$list->name} is full: a list holds at most " . DeckLimits::USER_DECK_MAX_CARDS . ' words.',
            ]);
        }

        $card = $this->lists->addWord(
            $list,
            $validated['front'],
            $validated['back'],
            $validated['description'] ?? null,
            $validated['pronunciation'] ?? null,
        );

        return response()->json([
            'created' => true,
            'card' => ['id' => $card->id, 'front' => $card->front, 'back' => $card->back],
            'list' => $this->lists->payload($list),
            'message' => "Added \"{$card->front}\" to {$list->name}.",
        ], 201);
    }

    /**
     * POST /api/word-lists
     *
     * A new, empty list, picked straight away: whoever makes a list from the
     * picker is about to save a word into it.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $user = $request->user();

        if ($user->hasReachedDeckLimit()) {
            throw ValidationException::withMessages([
                'name' => 'You have reached the maximum of ' . DeckLimits::USER_MAX_DECKS . ' decks and lists. Delete one before creating another.',
            ]);
        }

        // Not a rule for decks in general, but in a picker two lists with one
        // name cannot be told apart.
        if ($user->decks()->whereRaw('lower(name) = ?', [mb_strtolower($validated['name'])])->exists()) {
            throw ValidationException::withMessages(['name' => "You already have a list called {$validated['name']}."]);
        }

        $list = $this->lists->createList($user, $validated['name']);
        $this->lists->select($user, $list);

        return response()->json(['list' => $this->lists->payload($list)], 201);
    }

    /**
     * PUT /api/word-lists/selected
     *
     * Remembers the list picked; a null list_id goes back to the default list.
     */
    public function select(Request $request): JsonResponse
    {
        $validated = $request->validate(['list_id' => 'nullable|integer']);

        $list = isset($validated['list_id']) ? Deck::query()->findOrFail($validated['list_id']) : null;

        if ($list !== null) {
            $this->authorize('update', $list);
        }

        $this->lists->select($request->user(), $list);

        return response()->json(['list_id' => $list?->id]);
    }
}
