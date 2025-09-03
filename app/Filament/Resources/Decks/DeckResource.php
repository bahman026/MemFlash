<?php

namespace App\Filament\Resources\Decks;

use App\Filament\Resources\Decks\Pages\CreateDeck;
use App\Filament\Resources\Decks\Pages\EditDeck;
use App\Filament\Resources\Decks\Pages\ListDecks;
use App\Filament\Resources\Decks\Schemas\DeckForm;
use App\Filament\Resources\Decks\Tables\DecksTable;
use App\Models\Deck;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DeckResource extends Resource
{
    protected static ?string $model = Deck::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Deck';

    public static function form(Schema $schema): Schema
    {
        return DeckForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DecksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDecks::route('/'),
            'create' => CreateDeck::route('/create'),
            'edit' => EditDeck::route('/{record}/edit'),
        ];
    }
}
