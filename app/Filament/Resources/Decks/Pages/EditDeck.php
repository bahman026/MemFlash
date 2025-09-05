<?php

declare(strict_types=1);

namespace App\Filament\Resources\Decks\Pages;

use App\Filament\Resources\Decks\DeckResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDeck extends EditRecord
{
    protected static string $resource = DeckResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
