<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserLevelEnum;
use App\Models\StaticDeck;
use Illuminate\Database\Seeder;

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
                    'difficulty' => 'beginner',
                ],
            ],
            [
                'name' => 'American English File Starter - Lesson 2',
                'description' => 'Numbers, nationalities, languages, and question words for beginners.',
                'level' => UserLevelEnum::STARTER,
                'lesson_number' => 2,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 46,
                    'categories' => ['numbers', 'nationalities', 'languages', 'question_words', 'pronouns', 'classroom'],
                    'difficulty' => 'beginner',
                ],
            ],
            [
                'name' => 'American English File Starter - Lesson 3',
                'description' => 'Objects, clothing, money, transportation, and demonstratives for beginners.',
                'level' => UserLevelEnum::STARTER,
                'lesson_number' => 3,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 37,
                    'categories' => ['objects', 'clothing', 'money', 'transportation', 'demonstratives', 'articles', 'adjectives', 'drinks', 'question_phrases'],
                    'difficulty' => 'beginner',
                ],
            ],
            [
                'name' => 'American English File Starter - Lesson 4',
                'description' => 'People, family, adjectives, transportation, and colors for beginners.',
                'level' => UserLevelEnum::STARTER,
                'lesson_number' => 4,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 60,
                    'categories' => ['people', 'adjectives', 'possessive_pronouns', 'adverbs', 'family', 'transportation', 'places', 'general', 'colors'],
                    'difficulty' => 'beginner',
                ],
            ],
            [
                'name' => 'American English File Starter - Lesson 5',
                'description' => 'Food, drinks, meals, time, and verbs for beginners.',
                'level' => UserLevelEnum::STARTER,
                'lesson_number' => 5,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 58,
                    'categories' => ['food', 'body', 'time', 'drinks', 'verbs', 'prepositions', 'meals', 'adverbs', 'adjectives', 'articles'],
                    'difficulty' => 'beginner',
                ],
            ],
            [
                'name' => 'American English File Starter - Lesson 6',
                'description' => 'Jobs, daily routines, places, and adverbs of frequency for beginners.',
                'level' => UserLevelEnum::STARTER,
                'lesson_number' => 6,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 62,
                    'categories' => ['time', 'adverbs', 'people', 'places', 'meals', 'verbs', 'general', 'transportation', 'phrases'],
                    'difficulty' => 'beginner',
                ],
            ],
            [
                'name' => 'American English File Starter - Lesson 7',
                'description' => 'Dates, months, ordinal numbers, seasons, and activities for beginners.',
                'level' => UserLevelEnum::STARTER,
                'lesson_number' => 7,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 91,
                    'categories' => ['ordinal_numbers', 'people', 'months', 'prepositions', 'verbs', 'places', 'seasons', 'modal_verbs', 'adjectives', 'sports', 'phrases', 'signs', 'general', 'technology', 'instruments', 'mathematics', 'time'],
                    'difficulty' => 'beginner',
                ],
            ],
            [
                'name' => 'American English File Starter - Lesson 8',
                'description' => 'Travel, weather, and hotel-related vocabulary for beginners.',
                'level' => UserLevelEnum::STARTER,
                'lesson_number' => 8,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 36,
                    'categories' => ['places', 'adjectives', 'travel', 'weather', 'verbs', 'general', 'phrases'],
                    'difficulty' => 'beginner',
                ],
            ],
            [
                'name' => 'American English File Starter - Lesson 9',
                'description' => 'Places in a city, prepositions of place, and household items for beginners.',
                'level' => UserLevelEnum::STARTER,
                'lesson_number' => 9,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 62,
                    'categories' => ['places', 'objects', 'prepositions', 'general', 'verbs', 'money', 'transportation', 'adjectives', 'family', 'colors', 'demonstratives', 'articles', 'adverbs', 'question_phrases', 'time', 'body', 'meals', 'sports', 'modal_verbs', 'signs', 'technology', 'instruments', 'mathematics'],
                    'difficulty' => 'beginner',
                ],
            ],
            [
                'name' => 'American English File Starter - Lesson 10',
                'description' => 'Daily routines, personal care, and lifestyle vocabulary for beginners.',
                'level' => UserLevelEnum::STARTER,
                'lesson_number' => 10,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 23,
                    'categories' => ['places', 'verbs', 'transportation', 'sports', 'adjectives', 'general', 'phrases', 'objects'],
                    'difficulty' => 'beginner',
                ],
            ],
            [
                'name' => 'American English File Starter - Lesson 11',
                'description' => 'Social interactions, music preferences, and communication for beginners.',
                'level' => UserLevelEnum::STARTER,
                'lesson_number' => 11,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 50,
                    'categories' => ['verbs', 'general', 'pronouns', 'phrases', 'music', 'people', 'objects', 'adjectives', 'technology', 'adverbs'],
                    'difficulty' => 'beginner',
                ],
            ],
            [
                'name' => 'American English File Starter - Lesson 12',
                'description' => 'Future plans, time expressions, and grammar concepts for beginners.',
                'level' => UserLevelEnum::STARTER,
                'lesson_number' => 12,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 28,
                    'categories' => ['grammar', 'phrases', 'technology', 'education', 'general', 'time', 'sports', 'verbs'],
                    'difficulty' => 'beginner',
                ],
            ],
            [
                'name' => 'American English File 1 - Lesson 1',
                'description' => 'Numbers, countries, nationalities, and basic classroom language for elementary learners.',
                'level' => UserLevelEnum::ELEMENTARY,
                'lesson_number' => 1,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 207,
                    'difficulty' => 'elementary',
                ],
            ],
            [
                'name' => 'American English File 1 - Lesson 2',
                'description' => 'Adjectives, colors, objects, and descriptive language for elementary learners.',
                'level' => UserLevelEnum::ELEMENTARY,
                'lesson_number' => 2,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 133,
                    'difficulty' => 'elementary',
                ],
            ],
            [
                'name' => 'American English File 1 - Lesson 3',
                'description' => 'Jobs, professions, daily activities, and frequency adverbs for elementary learners.',
                'level' => UserLevelEnum::ELEMENTARY,
                'lesson_number' => 3,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 124,
                    'difficulty' => 'elementary',
                ],
            ],
            [
                'name' => 'American English File 1 - Lesson 4',
                'description' => 'Family relationships, daily routines, and time expressions for elementary learners.',
                'level' => UserLevelEnum::ELEMENTARY,
                'lesson_number' => 4,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 100,
                    'difficulty' => 'elementary',
                ],
            ],
            [
                'name' => 'American English File 1 - Lesson 5',
                'description' => 'Weather, seasons, clothing, and activities for elementary learners.',
                'level' => UserLevelEnum::ELEMENTARY,
                'lesson_number' => 5,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 80,
                    'difficulty' => 'elementary',
                ],
            ],
            [
                'name' => 'American English File 1 - Lesson 6',
                'description' => 'Music, months, ordinal numbers, and preferences for elementary learners.',
                'level' => UserLevelEnum::ELEMENTARY,
                'lesson_number' => 6,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 90,
                    'difficulty' => 'elementary',
                ],
            ],
            [
                'name' => 'American English File 1 - Lesson 7',
                'description' => 'Art, professions, directions, and past tense for elementary learners.',
                'level' => UserLevelEnum::ELEMENTARY,
                'lesson_number' => 7,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 85,
                    'difficulty' => 'elementary',
                ],
            ],
            [
                'name' => 'American English File 1 - Lesson 8',
                'description' => 'Houses, rooms, prepositions, and mystery stories for elementary learners.',
                'level' => UserLevelEnum::ELEMENTARY,
                'lesson_number' => 8,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 95,
                    'difficulty' => 'elementary',
                ],
            ],
            [
                'name' => 'American English File 1 - Lesson 9',
                'description' => 'Food, quantities, comparative adjectives, and numbers for elementary learners.',
                'level' => UserLevelEnum::ELEMENTARY,
                'lesson_number' => 9,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 110,
                    'difficulty' => 'elementary',
                ],
            ],
            [
                'name' => 'American English File 1 - Lesson 10',
                'description' => 'Places in town, superlative adjectives, and future plans for elementary learners.',
                'level' => UserLevelEnum::ELEMENTARY,
                'lesson_number' => 10,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 75,
                    'difficulty' => 'elementary',
                ],
            ],
            [
                'name' => 'American English File 1 - Lesson 11',
                'description' => 'Transportation, technology, adverbs, and travel for elementary learners.',
                'level' => UserLevelEnum::ELEMENTARY,
                'lesson_number' => 11,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 70,
                    'difficulty' => 'elementary',
                ],
            ],
            [
                'name' => 'American English File 1 - Lesson 12',
                'description' => 'Furniture, preferences, and review of elementary level vocabulary.',
                'level' => UserLevelEnum::ELEMENTARY,
                'lesson_number' => 12,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 60,
                    'difficulty' => 'elementary',
                ],
            ],
            // American English File 2 - Pre-Intermediate Level
            [
                'name' => 'American English File 2 - Lesson 1',
                'description' => 'Past simple, irregular verbs, and life events for pre-intermediate learners.',
                'level' => UserLevelEnum::PRE_INTERMEDIATE,
                'lesson_number' => 1,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 120,
                    'difficulty' => 'pre-intermediate',
                ],
            ],
            [
                'name' => 'American English File 2 - Lesson 2',
                'description' => 'Present perfect, experiences, and travel for pre-intermediate learners.',
                'level' => UserLevelEnum::PRE_INTERMEDIATE,
                'lesson_number' => 2,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 130,
                    'difficulty' => 'pre-intermediate',
                ],
            ],
            [
                'name' => 'American English File 2 - Lesson 3',
                'description' => 'Past continuous, storytelling, and narrative tenses for pre-intermediate learners.',
                'level' => UserLevelEnum::PRE_INTERMEDIATE,
                'lesson_number' => 3,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 125,
                    'difficulty' => 'pre-intermediate',
                ],
            ],
            [
                'name' => 'American English File 2 - Lesson 4',
                'description' => 'Future forms, predictions, and plans for pre-intermediate learners.',
                'level' => UserLevelEnum::PRE_INTERMEDIATE,
                'lesson_number' => 4,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 115,
                    'difficulty' => 'pre-intermediate',
                ],
            ],
            [
                'name' => 'American English File 2 - Lesson 5',
                'description' => 'Modal verbs, advice, and obligations for pre-intermediate learners.',
                'level' => UserLevelEnum::PRE_INTERMEDIATE,
                'lesson_number' => 5,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 140,
                    'difficulty' => 'pre-intermediate',
                ],
            ],
            [
                'name' => 'American English File 2 - Lesson 6',
                'description' => 'Conditionals, hypothetical situations, and possibilities for pre-intermediate learners.',
                'level' => UserLevelEnum::PRE_INTERMEDIATE,
                'lesson_number' => 6,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 135,
                    'difficulty' => 'pre-intermediate',
                ],
            ],
            [
                'name' => 'American English File 2 - Lesson 7',
                'description' => 'Passive voice, processes, and descriptions for pre-intermediate learners.',
                'level' => UserLevelEnum::PRE_INTERMEDIATE,
                'lesson_number' => 7,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 120,
                    'difficulty' => 'pre-intermediate',
                ],
            ],
            [
                'name' => 'American English File 2 - Lesson 8',
                'description' => 'Reported speech, indirect questions, and communication for pre-intermediate learners.',
                'level' => UserLevelEnum::PRE_INTERMEDIATE,
                'lesson_number' => 8,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 145,
                    'difficulty' => 'pre-intermediate',
                ],
            ],
            [
                'name' => 'American English File 2 - Lesson 9',
                'description' => 'Gerunds and infinitives, preferences, and verb patterns for pre-intermediate learners.',
                'level' => UserLevelEnum::PRE_INTERMEDIATE,
                'lesson_number' => 9,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 130,
                    'difficulty' => 'pre-intermediate',
                ],
            ],
            [
                'name' => 'American English File 2 - Lesson 10',
                'description' => 'Relative clauses, defining and non-defining for pre-intermediate learners.',
                'level' => UserLevelEnum::PRE_INTERMEDIATE,
                'lesson_number' => 10,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 125,
                    'difficulty' => 'pre-intermediate',
                ],
            ],
            [
                'name' => 'American English File 2 - Lesson 11',
                'description' => 'Articles, countable and uncountable nouns for pre-intermediate learners.',
                'level' => UserLevelEnum::PRE_INTERMEDIATE,
                'lesson_number' => 11,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 110,
                    'difficulty' => 'pre-intermediate',
                ],
            ],
            [
                'name' => 'American English File 2 - Lesson 12',
                'description' => 'Review and consolidation of pre-intermediate grammar and vocabulary.',
                'level' => UserLevelEnum::PRE_INTERMEDIATE,
                'lesson_number' => 12,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 100,
                    'difficulty' => 'pre-intermediate',
                ],
            ],
            // American English File 3 - Intermediate Level
            [
                'name' => 'American English File 3 - Lesson 1',
                'description' => 'Present perfect continuous, ongoing actions for intermediate learners.',
                'level' => UserLevelEnum::INTERMEDIATE,
                'lesson_number' => 1,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 150,
                    'difficulty' => 'intermediate',
                ],
            ],
            [
                'name' => 'American English File 3 - Lesson 2',
                'description' => 'Past perfect, narrative tenses, and storytelling for intermediate learners.',
                'level' => UserLevelEnum::INTERMEDIATE,
                'lesson_number' => 2,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 160,
                    'difficulty' => 'intermediate',
                ],
            ],
            [
                'name' => 'American English File 3 - Lesson 3',
                'description' => 'Future perfect, predictions, and advanced future forms for intermediate learners.',
                'level' => UserLevelEnum::INTERMEDIATE,
                'lesson_number' => 3,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 155,
                    'difficulty' => 'intermediate',
                ],
            ],
            [
                'name' => 'American English File 3 - Lesson 4',
                'description' => 'Mixed conditionals, complex hypothetical situations for intermediate learners.',
                'level' => UserLevelEnum::INTERMEDIATE,
                'lesson_number' => 4,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 165,
                    'difficulty' => 'intermediate',
                ],
            ],
            [
                'name' => 'American English File 3 - Lesson 5',
                'description' => 'Advanced modal verbs, speculation, and deduction for intermediate learners.',
                'level' => UserLevelEnum::INTERMEDIATE,
                'lesson_number' => 5,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 170,
                    'difficulty' => 'intermediate',
                ],
            ],
            [
                'name' => 'American English File 3 - Lesson 6',
                'description' => 'Subjunctive mood, formal language, and advanced structures for intermediate learners.',
                'level' => UserLevelEnum::INTERMEDIATE,
                'lesson_number' => 6,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 160,
                    'difficulty' => 'intermediate',
                ],
            ],
            [
                'name' => 'American English File 3 - Lesson 7',
                'description' => 'Inversion, emphasis, and advanced sentence structures for intermediate learners.',
                'level' => UserLevelEnum::INTERMEDIATE,
                'lesson_number' => 7,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 175,
                    'difficulty' => 'intermediate',
                ],
            ],
            [
                'name' => 'American English File 3 - Lesson 8',
                'description' => 'Advanced passive voice, complex processes for intermediate learners.',
                'level' => UserLevelEnum::INTERMEDIATE,
                'lesson_number' => 8,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 165,
                    'difficulty' => 'intermediate',
                ],
            ],
            [
                'name' => 'American English File 3 - Lesson 9',
                'description' => 'Advanced gerunds and infinitives, complex verb patterns for intermediate learners.',
                'level' => UserLevelEnum::INTERMEDIATE,
                'lesson_number' => 9,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 170,
                    'difficulty' => 'intermediate',
                ],
            ],
            [
                'name' => 'American English File 3 - Lesson 10',
                'description' => 'Advanced relative clauses, complex descriptions for intermediate learners.',
                'level' => UserLevelEnum::INTERMEDIATE,
                'lesson_number' => 10,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 160,
                    'difficulty' => 'intermediate',
                ],
            ],
            [
                'name' => 'American English File 3 - Lesson 11',
                'description' => 'Advanced articles, complex noun phrases for intermediate learners.',
                'level' => UserLevelEnum::INTERMEDIATE,
                'lesson_number' => 11,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 155,
                    'difficulty' => 'intermediate',
                ],
            ],
            [
                'name' => 'American English File 3 - Lesson 12',
                'description' => 'Review and consolidation of intermediate grammar and vocabulary.',
                'level' => UserLevelEnum::INTERMEDIATE,
                'lesson_number' => 12,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 140,
                    'difficulty' => 'intermediate',
                ],
            ],
            // American English File 4 - Upper-Intermediate Level
            [
                'name' => 'American English File 4 - Lesson 1',
                'description' => 'Advanced present perfect, complex time expressions for upper-intermediate learners.',
                'level' => UserLevelEnum::UPPER_INTERMEDIATE,
                'lesson_number' => 1,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 180,
                    'difficulty' => 'upper-intermediate',
                ],
            ],
            [
                'name' => 'American English File 4 - Lesson 2',
                'description' => 'Advanced past perfect, complex narrative structures for upper-intermediate learners.',
                'level' => UserLevelEnum::UPPER_INTERMEDIATE,
                'lesson_number' => 2,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 190,
                    'difficulty' => 'upper-intermediate',
                ],
            ],
            [
                'name' => 'American English File 4 - Lesson 3',
                'description' => 'Advanced future forms, complex predictions for upper-intermediate learners.',
                'level' => UserLevelEnum::UPPER_INTERMEDIATE,
                'lesson_number' => 3,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 185,
                    'difficulty' => 'upper-intermediate',
                ],
            ],
            [
                'name' => 'American English File 4 - Lesson 4',
                'description' => 'Advanced conditionals, complex hypothetical situations for upper-intermediate learners.',
                'level' => UserLevelEnum::UPPER_INTERMEDIATE,
                'lesson_number' => 4,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 195,
                    'difficulty' => 'upper-intermediate',
                ],
            ],
            [
                'name' => 'American English File 4 - Lesson 5',
                'description' => 'Advanced modal verbs, complex speculation for upper-intermediate learners.',
                'level' => UserLevelEnum::UPPER_INTERMEDIATE,
                'lesson_number' => 5,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 200,
                    'difficulty' => 'upper-intermediate',
                ],
            ],
            [
                'name' => 'American English File 4 - Lesson 6',
                'description' => 'Advanced subjunctive, complex formal language for upper-intermediate learners.',
                'level' => UserLevelEnum::UPPER_INTERMEDIATE,
                'lesson_number' => 6,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 190,
                    'difficulty' => 'upper-intermediate',
                ],
            ],
            [
                'name' => 'American English File 4 - Lesson 7',
                'description' => 'Advanced inversion, complex emphasis for upper-intermediate learners.',
                'level' => UserLevelEnum::UPPER_INTERMEDIATE,
                'lesson_number' => 7,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 205,
                    'difficulty' => 'upper-intermediate',
                ],
            ],
            [
                'name' => 'American English File 4 - Lesson 8',
                'description' => 'Advanced passive voice, complex academic language for upper-intermediate learners.',
                'level' => UserLevelEnum::UPPER_INTERMEDIATE,
                'lesson_number' => 8,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 195,
                    'difficulty' => 'upper-intermediate',
                ],
            ],
            [
                'name' => 'American English File 4 - Lesson 9',
                'description' => 'Advanced gerunds and infinitives, complex academic writing for upper-intermediate learners.',
                'level' => UserLevelEnum::UPPER_INTERMEDIATE,
                'lesson_number' => 9,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 200,
                    'difficulty' => 'upper-intermediate',
                ],
            ],
            [
                'name' => 'American English File 4 - Lesson 10',
                'description' => 'Advanced relative clauses, complex academic descriptions for upper-intermediate learners.',
                'level' => UserLevelEnum::UPPER_INTERMEDIATE,
                'lesson_number' => 10,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 190,
                    'difficulty' => 'upper-intermediate',
                ],
            ],
            [
                'name' => 'American English File 4 - Lesson 11',
                'description' => 'Advanced articles, complex academic language for upper-intermediate learners.',
                'level' => UserLevelEnum::UPPER_INTERMEDIATE,
                'lesson_number' => 11,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 185,
                    'difficulty' => 'upper-intermediate',
                ],
            ],
            [
                'name' => 'American English File 4 - Lesson 12',
                'description' => 'Review and consolidation of upper-intermediate grammar and vocabulary.',
                'level' => UserLevelEnum::UPPER_INTERMEDIATE,
                'lesson_number' => 12,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 170,
                    'difficulty' => 'upper-intermediate',
                ],
            ],
            // American English File 5 - Advanced Level
            [
                'name' => 'American English File 5 - Lesson 1',
                'description' => 'Mastery of present perfect, complex academic discourse for advanced learners.',
                'level' => UserLevelEnum::ADVANCED,
                'lesson_number' => 1,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 220,
                    'difficulty' => 'advanced',
                ],
            ],
            [
                'name' => 'American English File 5 - Lesson 2',
                'description' => 'Mastery of past perfect, complex narrative techniques for advanced learners.',
                'level' => UserLevelEnum::ADVANCED,
                'lesson_number' => 2,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 230,
                    'difficulty' => 'advanced',
                ],
            ],
            [
                'name' => 'American English File 5 - Lesson 3',
                'description' => 'Mastery of future forms, complex predictions and plans for advanced learners.',
                'level' => UserLevelEnum::ADVANCED,
                'lesson_number' => 3,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 225,
                    'difficulty' => 'advanced',
                ],
            ],
            [
                'name' => 'American English File 5 - Lesson 4',
                'description' => 'Mastery of conditionals, complex hypothetical reasoning for advanced learners.',
                'level' => UserLevelEnum::ADVANCED,
                'lesson_number' => 4,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 235,
                    'difficulty' => 'advanced',
                ],
            ],
            [
                'name' => 'American English File 5 - Lesson 5',
                'description' => 'Mastery of modal verbs, complex speculation and deduction for advanced learners.',
                'level' => UserLevelEnum::ADVANCED,
                'lesson_number' => 5,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 240,
                    'difficulty' => 'advanced',
                ],
            ],
            [
                'name' => 'American English File 5 - Lesson 6',
                'description' => 'Mastery of subjunctive, complex formal and academic language for advanced learners.',
                'level' => UserLevelEnum::ADVANCED,
                'lesson_number' => 6,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 230,
                    'difficulty' => 'advanced',
                ],
            ],
            [
                'name' => 'American English File 5 - Lesson 7',
                'description' => 'Mastery of inversion, complex emphasis and style for advanced learners.',
                'level' => UserLevelEnum::ADVANCED,
                'lesson_number' => 7,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 245,
                    'difficulty' => 'advanced',
                ],
            ],
            [
                'name' => 'American English File 5 - Lesson 8',
                'description' => 'Mastery of passive voice, complex academic and professional language for advanced learners.',
                'level' => UserLevelEnum::ADVANCED,
                'lesson_number' => 8,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 235,
                    'difficulty' => 'advanced',
                ],
            ],
            [
                'name' => 'American English File 5 - Lesson 9',
                'description' => 'Mastery of gerunds and infinitives, complex academic writing for advanced learners.',
                'level' => UserLevelEnum::ADVANCED,
                'lesson_number' => 9,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 240,
                    'difficulty' => 'advanced',
                ],
            ],
            [
                'name' => 'American English File 5 - Lesson 10',
                'description' => 'Mastery of relative clauses, complex academic descriptions for advanced learners.',
                'level' => UserLevelEnum::ADVANCED,
                'lesson_number' => 10,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 230,
                    'difficulty' => 'advanced',
                ],
            ],
            [
                'name' => 'American English File 5 - Lesson 11',
                'description' => 'Mastery of articles, complex academic and professional discourse for advanced learners.',
                'level' => UserLevelEnum::ADVANCED,
                'lesson_number' => 11,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 225,
                    'difficulty' => 'advanced',
                ],
            ],
            [
                'name' => 'American English File 5 - Lesson 12',
                'description' => 'Mastery review and consolidation of advanced grammar and vocabulary.',
                'level' => UserLevelEnum::ADVANCED,
                'lesson_number' => 12,
                'is_active' => true,
                'metadata' => [
                    'total_words' => 200,
                    'difficulty' => 'advanced',
                ],
            ],
        ];

        foreach ($decks as $deckData) {
            StaticDeck::updateOrCreate(
                [
                    'name' => $deckData['name'],
                    'level' => $deckData['level'],
                    'lesson_number' => $deckData['lesson_number'],
                ],
                $deckData
            );
        }

        $this->command->info('Seeded ' . count($decks) . ' static decks');
    }
}
