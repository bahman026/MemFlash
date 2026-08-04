<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cards\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('deck.name')
                    ->searchable(),
                TextColumn::make('front')
                    ->searchable(),
                TextColumn::make('back')
                    ->searchable(),
                TextColumn::make('state')
                    ->badge()
                    ->sortable(),
                TextColumn::make('stability')
                    ->label('S')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('difficulty')
                    ->label('D')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('lapses')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('due')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('last_review')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
