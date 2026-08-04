<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\StaticCard;
use App\Models\StaticDeck;
use App\Models\UserStaticDeckProgress;
use App\Models\UserStaticDeckSetting;
use App\Services\SpacedRepetitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StaticDeckController extends Controller
{
    public function __construct(
        private readonly SpacedRepetitionService $spacedRepetition,
    ) {}

    /**
     * Resolve how many cards this user studies per day for a deck.
     */
    private function cardsPerDayFor(StaticDeck $staticDeck): int
    {
        return (int) (UserStaticDeckSetting::where('user_id', auth()->id())
            ->where('static_deck_id', $staticDeck->id)
            ->value('cards_per_day') ?? 10);
    }

    /**
     * Reset learning progress for a static deck
     */
    public function reset(StaticDeck $staticDeck): RedirectResponse
    {
        $user = auth()->user();

        // Reset static cards learning progress
        $staticDeck->resetLearningProgress();

        // Reset user progress
        $userProgress = UserStaticDeckProgress::where('user_id', $user->id)
            ->where('static_deck_id', $staticDeck->id)
            ->first();

        if ($userProgress) {
            $userProgress->update([
                'cards_studied' => 0,
                'last_studied_at' => null,
                'completed_at' => null,
            ]);
        }

        return redirect()->back()->with('success', "Learning progress for {$staticDeck->name} has been reset successfully!");
    }

    /**
     * Show static deck details
     */
    public function show(StaticDeck $staticDeck)
    {
        $staticDeck->load('cards');

        return view('static-decks.show', compact('staticDeck'));
    }

    /**
     * Start studying a static deck
     */
    public function study(StaticDeck $staticDeck)
    {
        $staticDeck->load('cards');

        $cardsPerDay = $this->cardsPerDayFor($staticDeck);

        // Get cards that are due for review
        $dueCards = $staticDeck->cards()
            ->where(function ($query) {
                $query->whereNull('revised_at')
                    ->orWhere('revised_at', '<=', now());
            })
            ->orderByRaw('CASE WHEN revised_at IS NULL THEN 0 ELSE 1 END')
            ->orderBy('interval')
            ->limit($cardsPerDay)
            ->get();

        if ($dueCards->isEmpty()) {
            return redirect()->back()->with('info', 'All cards in this deck are up to date! Great job!');
        }

        return view('static-decks.study', compact('staticDeck', 'dueCards', 'cardsPerDay'));
    }

    /**
     * Preview static deck cards
     */
    public function preview(StaticDeck $staticDeck)
    {
        $staticDeck->load('cards');

        return view('static-decks.preview', compact('staticDeck'));
    }

    /**
     * Update cards per day setting for a static deck
     */
    public function updateCardsPerDay(Request $request, StaticDeck $staticDeck): RedirectResponse
    {
        $request->validate([
            'cards_per_day' => 'required|integer|min:1|max:100',
        ]);

        $user = auth()->user();

        $setting = UserStaticDeckSetting::firstOrCreate(
            [
                'user_id' => $user->id,
                'static_deck_id' => $staticDeck->id,
            ],
            [
                'cards_per_day' => 10,
                'is_active' => true,
            ]
        );

        $setting->update([
            'cards_per_day' => $request->cards_per_day,
        ]);

        return redirect()->back()->with('success', "Cards per day updated to {$request->cards_per_day} for {$staticDeck->name}!");
    }

    /**
     * Get cards for static deck study session
     */
    public function getCards(StaticDeck $staticDeck): JsonResponse
    {
        try {
            // Same daily cap as study(). Without the limit this endpoint handed back
            // every due card in the deck, so the cards-per-day setting was enforced
            // on the server-rendered page but silently bypassed via the JSON path.
            $cardsPerDay = $this->cardsPerDayFor($staticDeck);

            // Get cards that are due for review
            $dueCards = $staticDeck->cards()
                ->where(function ($query) {
                    $query->whereNull('revised_at')
                        ->orWhere('revised_at', '<=', now());
                })
                ->orderByRaw('CASE WHEN revised_at IS NULL THEN 0 ELSE 1 END')
                ->orderBy('interval')
                ->limit($cardsPerDay)
                ->get();

            return response()->json([
                'cards' => $dueCards,
                'deck' => [
                    'id' => $staticDeck->id,
                    'name' => $staticDeck->name,
                    'cards_per_day' => $cardsPerDay,
                    'cards_loaded' => $dueCards->count(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load cards'], 500);
        }
    }

    /**
     * Update a static card after study
     */
    public function updateCard(Request $request, StaticCard $card): JsonResponse
    {
        $request->validate([
            'quality' => 'required|integer|min:0|max:3',
        ]);

        try {
            $user = auth()->user();

            $this->spacedRepetition->review($card, (int) $request->quality);

            // Update user progress
            $userProgress = UserStaticDeckProgress::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'static_deck_id' => $card->static_deck_id,
                ],
                [
                    'cards_studied' => 0,
                    'total_cards' => $card->staticDeck->cards()->count(),
                ]
            );

            // Always increment progress for static cards (they're shared, not per-user)
            $userProgress->updateProgress(
                $userProgress->cards_studied + 1,
                [
                    'last_session_cards' => 1,
                    'last_session_date' => now()->toDateString(),
                ]
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update card'], 500);
        }
    }

    /**
     * Batch update static cards
     */
    public function batchUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'updates' => 'required|array',
            'updates.*.card_id' => 'required|integer',
            'updates.*.quality' => 'required|integer|min:0|max:3',
        ]);

        try {
            $user = auth()->user();
            $staticDeckId = null;
            $studiedCardIds = [];

            foreach ($request->updates as $update) {
                $card = StaticCard::find($update['card_id']);
                if ($card) {
                    $staticDeckId = $card->static_deck_id;

                    // Track which cards were studied
                    $studiedCardIds[] = $card->id;

                    $this->spacedRepetition->review($card, (int) $update['quality']);
                }
            }

            // Update user progress if we have a static deck
            if ($staticDeckId) {
                $staticDeck = StaticDeck::find($staticDeckId);
                if ($staticDeck) {
                    // Get or create user progress
                    $userProgress = UserStaticDeckProgress::firstOrCreate(
                        [
                            'user_id' => $user->id,
                            'static_deck_id' => $staticDeckId,
                        ],
                        [
                            'cards_studied' => 0,
                            'total_cards' => $staticDeck->cards()->count(),
                        ]
                    );

                    // Count unique cards studied in this session
                    $uniqueCardsStudied = count(array_unique($studiedCardIds));

                    // Update progress
                    $userProgress->updateProgress(
                        $userProgress->cards_studied + $uniqueCardsStudied,
                        [
                            'last_session_cards' => $uniqueCardsStudied,
                            'last_session_date' => now()->toDateString(),
                        ]
                    );
                }
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update cards'], 500);
        }
    }
}
