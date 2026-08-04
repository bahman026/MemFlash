<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\UserLevelEnum;
use App\Models\StaticDeck;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaticDeck>
 */
class StaticDeckFactory extends Factory
{
    protected $model = StaticDeck::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'American English File - Lesson ' . $this->faker->unique()->numberBetween(1, 12),
            'description' => $this->faker->sentence(),
            'level' => $this->faker->randomElement(UserLevelEnum::cases()),
            'lesson_number' => $this->faker->numberBetween(1, 12),
            'category' => $this->faker->randomElement(['vocabulary', 'grammar', 'phrases']),
            'language' => 'en',
            'is_active' => true,
            'sort_order' => 0,
            'metadata' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => ['is_active' => false]);
    }

    public function forLevel(UserLevelEnum $level): static
    {
        return $this->state(fn (array $attributes): array => ['level' => $level]);
    }
}
