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
                // FSRS memory state is derived by the scheduler from the review
                // history, so it is shown for diagnosis but not editable --
                // hand-editing stability would desync a card from its own log.
                TextInput::make('stability')
                    ->numeric()
                    ->disabled()
                    ->helperText('Days until recall falls to 90%. Derived by the scheduler.'),
                TextInput::make('difficulty')
                    ->numeric()
                    ->disabled()
                    ->helperText('1 is easiest, 10 is hardest.'),
                DateTimePicker::make('due'),
                DateTimePicker::make('last_review')
                    ->disabled(),
            ]);
    }
}
