<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Deck;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StudyController extends Controller
{
    use AuthorizesRequests;

    /**
     * Start a study session for a deck
     */
    public function start(Deck $deck): Response
    {
        // Temporarily disable authorization for debugging
        // $this->authorize('view', $deck);
        return response()->view('study.session', [
            'deck' => $deck,
        ]);
    }

    /**
     * Get cards for study session
     */
    public function getCards(Deck $deck): JsonResponse
    {
        // Temporarily disable authorization for debugging
        // $this->authorize('view', $deck);

        try {
            // Get new cards per day from deck settings
            $newCardsPerDay = $deck->new_cards_per_day ?? 10;

            // Get cards that are due for review (revised_at is null or in the past)
            $dueCards = $deck->cards()
                ->where(function ($query) {
                    $query->whereNull('revised_at')
                        ->orWhere('revised_at', '<=', now());
                })
                ->orderBy('revised_at', 'asc') // New cards (null) first, then by due date
                ->limit($newCardsPerDay)
                ->get();

            // Set revised_at to null for all cards we're about to show
            $dueCards->each(function ($card) {
                $card->update(['revised_at' => null]);
            });

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
        // Temporarily disable authorization for debugging
        // $this->authorize('view', $card->deck);

        $request->validate([
            'quality' => 'required|integer|min:0|max:3', // 0=Again, 1=Hard, 2=Good, 3=Easy
        ]);

        try {
            $quality = $request->input('quality');
            $now = now();

            // Only update revised_at if it's currently null (new card)
            if (! $card->revised_at) {
                switch ($quality) {
                    case 0: // Again - tomorrow
                        $card->revised_at = $now->copy()->addDay();

                        break;
                    case 1: // Hard - two days later
                        $card->revised_at = $now->copy()->addDays(2);

                        break;
                    case 2: // Good - 7 days later
                        $card->revised_at = $now->copy()->addDays(7);

                        break;
                    case 3: // Easy - 10 days later
                        $card->revised_at = $now->copy()->addDays(10);

                        break;
                }
            }

            $card->last_reviewed = $now;
            $card->save();

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

        try {
            $updates = $request->input('updates');
            $now = now();
            $updatedCards = [];

            foreach ($updates as $update) {
                $card = Card::find($update['card_id']);
                if (! $card) {
                    continue;
                }

                $quality = $update['quality'];

                // Only update revised_at if it's currently null (new card)
                if (! $card->revised_at) {
                    switch ($quality) {
                        case 0: // Again - tomorrow
                            $card->revised_at = $now->copy()->addDay();

                            break;
                        case 1: // Hard - two days later
                            $card->revised_at = $now->copy()->addDays(2);

                            break;
                        case 2: // Good - 7 days later
                            $card->revised_at = $now->copy()->addDays(7);

                            break;
                        case 3: // Easy - 10 days later
                            $card->revised_at = $now->copy()->addDays(10);

                            break;
                    }
                }

                $card->last_reviewed = $now;
                $card->save();

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
