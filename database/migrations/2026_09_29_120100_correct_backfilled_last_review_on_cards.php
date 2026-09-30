<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Repairs `last_review` on cards carried over from SM-2.
     *
     * The FSRS backfill (2026_08_04_120200) copied `last_review` from SM-2's
     * `last_reviewed`, which the old study code filled with the card's NEXT due
     * date. A migrated card therefore looked reviewed on the day it fell due, so
     * reviewing it that day counted zero elapsed days: the same-day formula ran,
     * a Good on a 10-day card gave S of about 9 instead of about 30, and the log
     * recorded R = 1 for the optimizer.
     *
     * The SM-2 columns are gone, so the backfill cannot simply be rerun. What is
     * left is enough: the backfill set `due` to the old due date and `stability`
     * to the old interval, so the review happened `stability` days before `due`.
     *
     * Only rows with last_review >= due are touched. A genuine FSRS review can
     * never produce that for a card in Review, which is always due at least a day
     * after it was last reviewed, so a card reviewed since the migration is left
     * alone. Running this twice is harmless.
     */
    public function up(): void
    {
        DB::table('cards')
            ->where('state', 'review')
            ->whereNotNull('last_review')
            ->whereNotNull('due')
            ->whereNotNull('stability')
            ->whereColumn('last_review', '>=', 'due')
            ->update([
                'last_review' => DB::raw('due - make_interval(days => GREATEST(ROUND(stability)::integer, 1))'),
            ]);
    }

    /**
     * Not reversible: the wrong values are not worth restoring.
     */
    public function down(): void
    {
        //
    }
};
