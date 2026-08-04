<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\StaticDeck;
use App\Models\User;
use App\Models\UserStaticDeckSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserStaticDeckSetting>
 */
class UserStaticDeckSettingFactory extends Factory
{
    protected $model = UserStaticDeckSetting::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'static_deck_id' => StaticDeck::factory(),
            'cards_per_day' => 10,
            'is_active' => true,
        ];
    }

    public function cardsPerDay(int $count): static
    {
        return $this->state(fn (array $attributes): array => ['cards_per_day' => $count]);
    }
}
