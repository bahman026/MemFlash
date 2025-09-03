<?php

namespace App\Filament\Resources\Decks\Pages;

use App\Filament\Resources\Decks\DeckResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDeck extends CreateRecord
{
    protected static string $resource = DeckResource::class;
}
