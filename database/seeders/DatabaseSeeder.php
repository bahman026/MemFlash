<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // One transaction for the whole curriculum. The entrypoint only seeds while
        // static_decks is empty, and StaticDeckSeeder writes all 66 decks before the
        // ~5,000 cards, so a seed killed part-way (a restart during the first boot,
        // or one card seeder throwing) used to leave the decks behind and was never
        // retried. Rolled back, the next boot sees an empty table and seeds again.
        DB::transaction(fn () => $this->call([
            AdminSeeder::class,
            StaticDeckSeeder::class,
            StaticCardStarterSeeder::class,
            StaticCardFile1Seeder::class,
            StaticCardFile2Seeder::class,
            StaticCardFile3Seeder::class,
            StaticCardFile4Seeder::class,
            StaticCardFile5Seeder::class,
        ]));
    }
}
