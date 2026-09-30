<?php

declare(strict_types=1);

use App\Fsrs\Parameters;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds FSRS memory state to personal cards.
     *
     * The old SM-2 columns (interval, ease_factor, repetitions, revised_at,
     * last_reviewed) are deliberately left in place here so this migration is
     * safe to deploy before the controllers are wired to the new scheduler. A
     * follow-up migration drops them once nothing reads them.
     *
     * Stability is `double`, not `float`: values legitimately reach tens of
     * thousands of days and single precision loses accuracy in that tail.
     */
    public function up(): void
    {
        Schema::table('cards', function (Blueprint $table): void {
            $table->string('state', 12)->default('new')->after('audio');
            $table->smallInteger('step')->nullable()->after('state');
            $table->double('stability')->nullable()->after('step');
            $table->double('difficulty')->nullable()->after('stability');
            $table->timestamp('due')->nullable()->after('difficulty');
            $table->timestamp('last_review')->nullable()->after('due');
            $table->integer('reps')->default(0)->after('last_review');
            $table->integer('lapses')->default(0)->after('reps');
            $table->boolean('suspended')->default(false)->after('lapses');

            // The queue query runs on every page load.
            $table->index(['deck_id', 'state', 'due']);
        });

        $this->backfill();
    }

    /**
     * Carry SM-2 progress across.
     *
     * There is no exact conversion, so both estimates are documented rather than
     * hidden:
     *
     * Stability. The old interval was roughly the point at which recall had
     * decayed to the target retention, and that is precisely the definition of
     * stability. S = interval preserves momentum instead of resetting every card.
     *
     * Difficulty. FSRS has no ease_factor, but ease_factor was the old difficulty
     * proxy: lower ease meant a harder card. The mapping is anchored so that an
     * untouched ease of 2.5 lands on D0(Good) = 2.1181 -- the difficulty a card
     * would get from answering Good on its first review -- and the SM-2 floor of
     * 1.3 lands on the maximum difficulty of 10. It is linear in between.
     *
     * Note that `interval` is quoted throughout: it is a reserved type keyword in
     * PostgreSQL and unquoted references are a syntax error.
     */
    private function backfill(): void
    {
        $sMin = Parameters::S_MIN;

        // D0(Good) with the default parameters.
        $dAtDefaultEase = 2.1181;
        $slope = (10.0 - $dAtDefaultEase) / (2.5 - 1.3); // ~6.5682 per ease point lost

        $difficulty = sprintf(
            'LEAST(10, GREATEST(1, %F + (2.5 - COALESCE(ease_factor, 2.5)::double precision) * %F))',
            $dAtDefaultEase,
            $slope
        );

        // Reviewed at least once -> Review state, seeded from the old schedule.
        DB::table('cards')->whereNotNull('last_reviewed')->update([
            'state' => 'review',
            'stability' => DB::raw('GREATEST(COALESCE("interval", 1)::double precision, ' . $sMin . ')'),
            'difficulty' => DB::raw($difficulty),
            'due' => DB::raw('COALESCE(revised_at, NOW())'),
            // Not last_reviewed: until 793196a the SM-2 code wrote it from the same
            // Carbon instance it had just advanced to the due date, so it held the
            // NEXT due date. The real last review is the due date minus the interval.
            'last_review' => DB::raw('CASE WHEN revised_at IS NOT NULL THEN revised_at - make_interval(days => COALESCE("interval", 1)) ELSE last_reviewed END'),
            'reps' => DB::raw('GREATEST(COALESCE(repetitions, 0), 1)'),
        ]);

        // Never reviewed -> stays New. Keep any pre-existing due date.
        DB::table('cards')->whereNull('last_reviewed')->update([
            'state' => 'new',
            'due' => DB::raw('COALESCE(revised_at, NOW())'),
        ]);
    }

    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table): void {
            $table->dropIndex(['deck_id', 'state', 'due']);
            $table->dropColumn([
                'state', 'step', 'stability', 'difficulty',
                'due', 'last_review', 'reps', 'lapses', 'suspended',
            ]);
        });
    }
};
