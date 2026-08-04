<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Deck;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Deck>
 */
class DeckFactory extends Factory
{
    protected $model = Deck::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'user_id' => User::factory(), // creates a user if none exists
            // Deliberately NOT random. This used to be faker->boolean(30), which
            // made every authorization test flaky: DeckPolicy::view() passes for a
            // public deck, so a test asserting 403 for a stranger's deck failed
            // roughly 30% of runs. Visibility is opt-in through public() instead.
            'is_public' => false,
            'new_cards_per_day' => $this->faker->numberBetween(5, 20),
        ];
    }

    public function public(): static
    {
        return $this->state(fn (array $attributes): array => ['is_public' => true]);
    }

    public function private(): static
    {
        return $this->state(fn (array $attributes): array => ['is_public' => false]);
    }
}
