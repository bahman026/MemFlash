<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Deck;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CardController extends Controller
{
    use AuthorizesRequests;

    /**
     * Show the form for creating a new card
     */
    public function create(Deck $deck): View
    {
        $this->authorize('update', $deck);

        return view('cards.create', compact('deck'));
    }

    /**
     * Store a newly created card
     */
    public function store(Request $request, Deck $deck): RedirectResponse
    {
        Log::info('Card store method called', [
            'deck_id' => $deck->id,
            'deck_name' => $deck->name,
            'user_id' => auth()->id(),
            'request_data' => $request->all()
        ]);

        $this->authorize('update', $deck);

        $request->validate([
            'front' => 'required|string|max:1000',
            'back' => 'required|string|max:1000',
        ], [
            'front.required' => 'Front text is required.',
            'front.max' => 'Front text must not exceed 1000 characters.',
            'back.required' => 'Back text is required.',
            'back.max' => 'Back text must not exceed 1000 characters.',
        ]);

        // Check if deck has reached card limit
        if ($deck->hasReachedCardLimit()) {
            Log::warning('Deck has reached card limit', [
                'deck_id' => $deck->id,
                'current_cards' => $deck->cards()->count(),
                'max_cards' => \App\Constants\DeckLimits::USER_DECK_MAX_CARDS
            ]);
            return back()->withErrors(['card' => 'This deck has reached the maximum number of cards allowed.']);
        }

        $card = $deck->cards()->create([
            'front' => $request->front,
            'back' => $request->back,
        ]);

        Log::info('Card created successfully', [
            'card_id' => $card->id,
            'deck_id' => $deck->id,
            'front' => $card->front,
            'back' => $card->back
        ]);

        return redirect()->route('decks.show', $deck)->with('success', 'Card added successfully!');
    }

    /**
     * Show the form for editing a card
     */
    public function edit(Card $card): View
    {
        $this->authorize('update', $card->deck);

        return view('cards.edit', compact('card'));
    }

    /**
     * Update the specified card
     */
    public function update(Request $request, Card $card): RedirectResponse
    {
        $this->authorize('update', $card->deck);

        $request->validate([
            'front' => 'required|string|max:1000',
            'back' => 'required|string|max:1000',
        ], [
            'front.required' => 'Front text is required.',
            'front.max' => 'Front text must not exceed 1000 characters.',
            'back.required' => 'Back text is required.',
            'back.max' => 'Back text must not exceed 1000 characters.',
        ]);

        $card->update([
            'front' => $request->front,
            'back' => $request->back,
        ]);

        return redirect()->route('decks.show', $card->deck)->with('success', 'Card updated successfully!');
    }

    /**
     * Remove the specified card
     */
    public function destroy(Card $card): RedirectResponse
    {
        $this->authorize('update', $card->deck);

        $deck = $card->deck;
        $card->delete();

        return redirect()->route('decks.show', $deck)->with('success', 'Card removed successfully!');
    }
}
