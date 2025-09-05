<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Deck;
use Livewire\Component;

class DeckList extends Component
{
    public function render(): \Illuminate\Contracts\View\Factory | \Illuminate\Contracts\View\View
    {
        return view('livewire.deck-list', [
            'decks' => Deck::query()->withCount('cards')->get(),
        ]);
    }
}
