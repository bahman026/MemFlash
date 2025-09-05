<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cards\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('deck_id')
                    ->relationship('deck', 'name')
                    ->required(),
                TextInput::make('front')
                    ->required(),
                TextInput::make('back')
                    ->required(),
                TextInput::make('audio'),
                TextInput::make('interval')
                    ->required()
                    ->numeric()
                    ->default(1),
                DateTimePicker::make('revised_at'),
                DateTimePicker::make('last_reviewed'),
            ]);
    }
}
