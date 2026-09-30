<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * cards.front and cards.back were varchar(255) while the card forms accept up
     * to 1000 characters, so a longer definition passed validation and then
     * failed the insert with a 500, and one long row made a whole import fail.
     *
     * In PostgreSQL varchar -> text is a catalogue-only change: no table rewrite,
     * no index rebuild.
     */
    public function up(): void
    {
        Schema::table('cards', function (Blueprint $table): void {
            $table->text('front')->change();
            $table->text('back')->change();
        });
    }

    /**
     * Left as text: narrowing back to 255 would fail on, or cut, any longer card.
     */
    public function down(): void
    {
        //
    }
};
