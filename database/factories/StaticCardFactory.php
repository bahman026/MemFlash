<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\StaticCard;
use App\Models\StaticDeck;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaticCard>
 */
class StaticCardFactory extends Factory
{
    protected $model = StaticCard::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'static_deck_id' => StaticDeck::factory(),
            'front' => $this->faker->word(),
            'back' => $this->faker->word(),
            'audio' => null,
            'interval' => 1,
            'ease_factor' => 2.5,
            'repetitions' => 0,
            'revised_at' => null,
            'last_reviewed' => null,
        ];
    }

    /**
     * A card that is due for review right now.
     */
    public function due(): static
    {
        return $this->state(fn (array $attributes): array => [
            'revised_at' => now()->subDay(),
            'last_reviewed' => now()->subDays(2),
        ]);
    }

    /**
     * A card scheduled well into the future.
     */
    public function notDue(): static
    {
        return $this->state(fn (array $attributes): array => [
            'revised_at' => now()->addWeek(),
            'last_reviewed' => now(),
        ]);
    }
}
