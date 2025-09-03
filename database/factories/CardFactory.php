<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Card;
use App\Models\Deck;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Card>
 */
class CardFactory extends Factory
{
    protected $model = Card::class;

    public function definition(): array
    {
        return [
            'deck_id' => Deck::factory(), // create a deck if none exists
            'front' => $this->faker->sentence(3),
            'back' => $this->faker->sentence(6),
            'audio' => null, // optional, can later store a path or base64
            'interval' => 1,
            'ease' => 2.5,
            'last_reviewed' => null,
        ];
    }
}
