<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StaticDeck;
use App\Enums\UserLevelEnum;

class StaticDeckSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $decks = [
            [
                'name' => 'American English File Starter - Lesson 1',
                'description' => 'Basic vocabulary and phrases for beginners - Numbers, countries, classroom objects, and greetings.',
                'level' => UserLevelEnum::STARTER,
                'lesson_number' => 1,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 90,
                    'categories' => ['numbers', 'countries', 'classroom', 'greetings', 'days'],
                    'difficulty' => 'beginner'
                ]
            ],
            [
                'name' => 'American English File Starter - Lesson 2',
                'description' => 'Lesson 2 vocabulary and phrases.',
                'level' => UserLevelEnum::STARTER,
                'lesson_number' => 2,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 0,
                    'categories' => [],
                    'difficulty' => 'beginner'
                ]
            ],
            [
                'name' => 'American English File Starter - Lesson 3',
                'description' => 'Lesson 3 vocabulary and phrases.',
                'level' => UserLevelEnum::STARTER,
                'lesson_number' => 3,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 0,
                    'categories' => [],
                    'difficulty' => 'beginner'
                ]
            ],
            [
                'name' => 'American English File Starter - Lesson 4',
                'description' => 'Lesson 4 vocabulary and phrases.',
                'level' => UserLevelEnum::STARTER,
                'lesson_number' => 4,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 0,
                    'categories' => [],
                    'difficulty' => 'beginner'
                ]
            ],
            [
                'name' => 'American English File Starter - Lesson 5',
                'description' => 'Lesson 5 vocabulary and phrases.',
                'level' => UserLevelEnum::STARTER,
                'lesson_number' => 5,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 0,
                    'categories' => [],
                    'difficulty' => 'beginner'
                ]
            ],
            [
                'name' => 'American English File Starter - Lesson 6',
                'description' => 'Lesson 6 vocabulary and phrases.',
                'level' => UserLevelEnum::STARTER,
                'lesson_number' => 6,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 0,
                    'categories' => [],
                    'difficulty' => 'beginner'
                ]
            ],
            [
                'name' => 'American English File Starter - Lesson 7',
                'description' => 'Lesson 7 vocabulary and phrases.',
                'level' => UserLevelEnum::STARTER,
                'lesson_number' => 7,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 0,
                    'categories' => [],
                    'difficulty' => 'beginner'
                ]
            ],
            [
                'name' => 'American English File Starter - Lesson 8',
                'description' => 'Lesson 8 vocabulary and phrases.',
                'level' => UserLevelEnum::STARTER,
                'lesson_number' => 8,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 0,
                    'categories' => [],
                    'difficulty' => 'beginner'
                ]
            ],
            [
                'name' => 'American English File Starter - Lesson 9',
                'description' => 'Lesson 9 vocabulary and phrases.',
                'level' => UserLevelEnum::STARTER,
                'lesson_number' => 9,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 0,
                    'categories' => [],
                    'difficulty' => 'beginner'
                ]
            ],
            [
                'name' => 'American English File Starter - Lesson 10',
                'description' => 'Lesson 10 vocabulary and phrases.',
                'level' => UserLevelEnum::STARTER,
                'lesson_number' => 10,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 0,
                    'categories' => [],
                    'difficulty' => 'beginner'
                ]
            ],
            [
                'name' => 'American English File Starter - Lesson 11',
                'description' => 'Lesson 11 vocabulary and phrases.',
                'level' => UserLevelEnum::STARTER,
                'lesson_number' => 11,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 0,
                    'categories' => [],
                    'difficulty' => 'beginner'
                ]
            ],
            [
                'name' => 'American English File Starter - Lesson 12',
                'description' => 'Lesson 12 vocabulary and phrases.',
                'level' => UserLevelEnum::STARTER,
                'lesson_number' => 12,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 0,
                    'categories' => [],
                    'difficulty' => 'beginner'
                ]
            ],
        ];

        foreach ($decks as $deckData) {
            StaticDeck::updateOrCreate(
                [
                    'name' => $deckData['name'],
                    'level' => $deckData['level'],
                    'lesson_number' => $deckData['lesson_number']
                ],
                $deckData
            );
        }
        
        $this->command->info('Seeded ' . count($decks) . ' static decks');
    }
}