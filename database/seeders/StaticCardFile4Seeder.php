<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\StaticCard;
use App\Models\StaticDeck;
use Illuminate\Database\Seeder;

class StaticCardFile4Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all Upper-intermediate level static decks
        $upperIntermediateDecks = StaticDeck::where('level', 'upper_intermediate')->get();

        if ($upperIntermediateDecks->isEmpty()) {
            $this->command->error('No Upper-intermediate level static decks found. Please run StaticDeckSeeder first.');

            return;
        }

        // Add lessons as they become available
        $this->command->info('No vocabulary added yet for American English File 4 (Upper-intermediate level)');
    }

    private function seedVocabulary($deck, $vocabulary, $lessonName)
    {
        foreach ($vocabulary as $cardData) {
            StaticCard::updateOrCreate(
                [
                    'static_deck_id' => $deck->id,
                    'front' => $cardData['front'],
                    'back' => $cardData['back'],
                ],
                [
                    'static_deck_id' => $deck->id,
                    'front' => $cardData['front'],
                    'back' => $cardData['back'],
                    'interval' => 1,
                    'revised_at' => null,
                    'last_reviewed' => null,
                ]
            );
        }

        $this->command->info('Seeded ' . count($vocabulary) . ' cards for ' . $lessonName);
    }
}
