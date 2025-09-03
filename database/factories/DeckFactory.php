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

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'user_id' => User::factory(), // creates a user if none exists
            'is_public' => $this->faker->boolean(30), // 30% chance to be public
            'new_cards_per_day' => $this->faker->numberBetween(5, 20),
        ];
    }
}
