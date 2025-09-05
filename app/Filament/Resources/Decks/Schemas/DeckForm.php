<?php

declare(strict_types=1);

namespace App\Filament\Resources\Decks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DeckForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Toggle::make('is_public')
                    ->required(),
                TextInput::make('new_cards_per_day')
                    ->required()
                    ->numeric()
                    ->default(10),
            ]);
    }
}
