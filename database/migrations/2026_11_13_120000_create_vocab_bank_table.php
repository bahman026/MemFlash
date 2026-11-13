<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An English ↔ Persian word bank on the server, filled by
     * `php artisan words:import-vocab-bank` from Wiktionary's Persian
     * dictionary (see App\Services\WordLookup\VocabBank). One row per Persian
     * word and English meaning; the keys are normalized for matching.
     *
     * rank orders the answers for one key, lowest first.
     */
    public function up(): void
    {
        Schema::create('vocab_bank', function (Blueprint $table): void {
            $table->id();
            $table->string('english');
            $table->string('english_key')->index();
            $table->string('persian');
            $table->string('persian_key')->index();
            $table->string('part_of_speech', 32)->nullable();
            $table->unsignedInteger('rank');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vocab_bank');
    }
};
