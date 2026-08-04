<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\StaticDeck;
use App\Models\User;
use App\Models\UserStaticDeckProgress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserStaticDeckProgress>
 */
class UserStaticDeckProgressFactory extends Factory
{
    protected $model = UserStaticDeckProgress::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'static_deck_id' => StaticDeck::factory(),
            'cards_studied' => 0,
            'total_cards' => 10,
            'last_studied_at' => null,
            'completed_at' => null,
            'progress_data' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'cards_studied' => $attributes['total_cards'] ?? 10,
            'last_studied_at' => now(),
            'completed_at' => now(),
        ]);
    }
}
