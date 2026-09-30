<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An optional third field on personal cards: an example sentence, a usage
     * note, anything shown under the answer once it is revealed.
     *
     * `text`, not `string`: examples run longer than a word or a gloss, and a
     * varchar(255) would turn one long sentence in an import into a failed insert.
     */
    public function up(): void
    {
        Schema::table('cards', function (Blueprint $table): void {
            $table->text('description')->nullable()->after('back');
        });
    }

    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table): void {
            $table->dropColumn('description');
        });
    }
};
