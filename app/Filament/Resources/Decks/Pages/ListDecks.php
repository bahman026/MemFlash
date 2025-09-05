<?php

declare(strict_types=1);

namespace App\Filament\Resources\Decks\Pages;

use App\Filament\Resources\Decks\DeckResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDecks extends ListRecords
{
    protected static string $resource = DeckResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
