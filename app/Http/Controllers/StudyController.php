<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Deck;
use App\Services\SpacedRepetitionService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StudyController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly SpacedRepetitionService $spacedRepetition,
    ) {}

    /**
     * Start a study session for a deck
     */
    public function start(Deck $deck): Response
    {
        $this->authorize('view', $deck);

        return response()->view('study.session', [
            'deck' => $deck,
        ]);
    }

    /**
     * Get cards for study session
     */
    public function getCards(Deck $deck): JsonResponse
    {
        $this->authorize('view', $deck);

        try {
            // Get new cards per day from deck settings
            $newCardsPerDay = $deck->new_cards_per_day ?? 10;

            // Get cards that are due for review (revised_at is null or in the past)
            $dueCards = $deck->cards()
                ->where(function ($query) {
                    $query->whereNull('revised_at')
                        ->orWhere('revised_at', '<=', now());
                })
                ->orderByRaw('CASE WHEN revised_at IS NULL THEN 0 ELSE 1 END')
                ->orderBy('revised_at', 'asc') // New cards (null) first, then by due date
                ->limit($newCardsPerDay)
                ->get();

            $cards = $dueCards->map(function ($card) {
                return [
                    'id' => $card->id,
                    'front' => $card->front,
                    'back' => $card->back,
                    'interval' => $card->interval,
                    'revised_at' => $card->revised_at,
                    'last_reviewed' => $card->last_reviewed,
                ];
            });

            $deckInfo = [
                'id' => $deck->id,
                'name' => $deck->name,
                'total_cards' => $deck->cards()->count(),
                'new_cards_per_day' => $newCardsPerDay,
                'cards_loaded' => $cards->count(),
            ];

            return response()->json([
                'cards' => $cards,
                'deck' => $deckInfo,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load cards: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update a single card after rating
     */
    public function updateCard(Request $request, Card $card): JsonResponse
    {
        // 'update' rather than 'view': reviewing mutates the card's schedule, and
        // DeckPolicy::view() also passes for public decks owned by someone else.
        $this->authorize('update', $card->deck);

        $request->validate([
            'quality' => 'required|integer|min:0|max:3', // 0=Again, 1=Hard, 2=Good, 3=Easy
        ]);

        try {
            $this->spacedRepetition->review($card, (int) $request->input('quality'));

            return response()->json([
                'success' => true,
                'card' => [
                    'id' => $card->id,
                    'revised_at' => $card->revised_at,
                    'last_reviewed' => $card->last_reviewed,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update card: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Batch update multiple cards
     */
    public function batchUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'updates' => 'required|array',
            'updates.*.card_id' => 'required|integer|exists:cards,id',
            'updates.*.quality' => 'required|integer|min:0|max:3',
        ]);

        $updates = $request->input('updates');

        // Authorize every card up front, outside the try/catch below.
        // AuthorizationException extends Exception, so authorizing inside the try
        // would swallow a 403 and report it as a generic 500 instead.
        //
        // Validating only `exists:cards,id` proves the card exists, not that the
        // caller owns it -- without this check any authenticated user could
        // rewrite another user's scheduling by guessing card ids.
        $cards = Card::with('deck')
            ->findMany(array_column($updates, 'card_id'))
            ->keyBy('id');

        foreach ($cards as $card) {
            $this->authorize('update', $card->deck);
        }

        try {
            $updatedCards = [];

            foreach ($updates as $update) {
                $card = $cards->get($update['card_id']);
                if (! $card) {
                    continue;
                }

                $this->spacedRepetition->review($card, (int) $update['quality']);

                $updatedCards[] = [
                    'id' => $card->id,
                    'revised_at' => $card->revised_at,
                    'last_reviewed' => $card->last_reviewed,
                ];
            }

            return response()->json([
                'success' => true,
                'updated_cards' => $updatedCards,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update cards: ' . $e->getMessage()], 500);
        }
    }
}
