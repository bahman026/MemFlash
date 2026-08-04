<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserLevelEnum;
use App\Enums\UserStatusEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                // The User model casts `password` to 'hashed', so Filament's plain
                // input is hashed on save and an existing hash is not re-hashed.
                // These modifiers stop the stored hash being loaded back into the
                // field and only write the column when a new value is typed.
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->helperText('Leave blank to keep the current password.')
                    ->afterStateHydrated(fn (TextInput $component) => $component->state(null)),
                TextInput::make('avatar'),
                Select::make('status')
                    ->options(UserStatusEnum::class)
                    ->default(1)
                    ->required(),
                Select::make('level')
                    ->options(UserLevelEnum::getOptions())
                    ->default(UserLevelEnum::STARTER->value)
                    ->required(),
            ]);
    }
}
