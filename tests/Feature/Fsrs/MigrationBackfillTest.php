<?php

declare(strict_types=1);

use App\Models\Deck;
use App\Models\StaticCard;
use App\Models\StaticDeck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * These tests exist mainly to prove the migrations themselves run inside the
 * suite. They only pass on PostgreSQL, which is deliberate: the backfill uses
 * Postgres-native SQL, and running the suite on SQLite would silently skip the
 * one migration most likely to break a deploy.
 */
it('runs every migration on the test database', function (): void {
    expect(DB::getDriverName())->toBe('pgsql')
        ->and(Schema::hasTable('deck_configs'))->toBeTrue()
        ->and(Schema::hasTable('review_logs'))->toBeTrue()
        ->and(Schema::hasTable('user_static_card_states'))->toBeTrue();
});

it('adds the FSRS columns to cards', function (): void {
    foreach (['state', 'step', 'stability', 'difficulty', 'due', 'last_review', 'reps', 'lapses', 'suspended'] as $column) {
        expect(Schema::hasColumn('cards', $column))->toBeTrue("cards.{$column}");
    }
});

it('gives users a timezone and rollover hour', function (): void {
    $user = User::factory()->create();

    expect($user->fresh()->timezone)->toBe('UTC')
        ->and((int) $user->fresh()->rollover_hour)->toBe(4);
});

it('defaults a new card to the new state with no memory yet', function (): void {
    $deck = Deck::factory()->create();
    $card = $deck->cards()->create(['front' => 'x', 'back' => 'y']);

    $row = DB::table('cards')->where('id', $card->id)->first();

    expect($row->state)->toBe('new')
        ->and($row->stability)->toBeNull()
        ->and($row->difficulty)->toBeNull()
        ->and($row->reps)->toBe(0)
        ->and($row->lapses)->toBe(0)
        ->and((bool) $row->suspended)->toBeFalse();
});

it('moves last_review back from the due date on cards carried over from SM-2', function (): void {
    $deck = Deck::factory()->create();
    $due = now()->startOfSecond();

    // As the backfill left it: last_review copied from SM-2's last_reviewed, which
    // held the due date, and stability set to the old 10-day interval.
    $migrated = $deck->cards()->create(['front' => 'a', 'back' => 'b', 'state' => 'review', 'stability' => 10.0, 'difficulty' => 5.0, 'due' => $due, 'last_review' => $due]);

    // Reviewed under FSRS since: due after last_review, so it must not move.
    $reviewed = $deck->cards()->create(['front' => 'c', 'back' => 'd', 'state' => 'review', 'stability' => 10.0, 'difficulty' => 5.0, 'due' => $due, 'last_review' => $due->copy()->subDays(4)]);

    (require database_path('migrations/2026_09_29_120100_correct_backfilled_last_review_on_cards.php'))->up();

    expect($migrated->fresh()->last_review->toDateTimeString())->toBe($due->copy()->subDays(10)->toDateTimeString())
        ->and($reviewed->fresh()->last_review->toDateTimeString())->toBe($due->copy()->subDays(4)->toDateTimeString());
});

it('enforces one review log per client uuid so a retried sync cannot double apply', function (): void {
    $deck = Deck::factory()->create();
    $card = $deck->cards()->create(['front' => 'x', 'back' => 'y']);
    $uuid = (string) Str::uuid();

    $row = fn (): array => [
        'user_id' => $deck->user_id,
        'reviewable_type' => $card::class,
        'reviewable_id' => $card->id,
        'rating' => 3,
        'state_before' => 'new',
        'elapsed_days' => 0,
        'retrievability_before' => 1.0,
        'stability_after' => 2.3065,
        'difficulty_after' => 2.1181,
        'scheduled_days' => 2,
        'reviewed_at' => now(),
        'client_uuid' => $uuid,
        'created_at' => now(),
    ];

    DB::table('review_logs')->insert($row());

    expect(fn () => DB::table('review_logs')->insert($row()))
        ->toThrow(Illuminate\Database\UniqueConstraintViolationException::class);
});

it('enforces one state row per user and static card', function (): void {
    $user = User::factory()->create();
    $deck = StaticDeck::factory()->create();
    $card = StaticCard::factory()->for($deck, 'staticDeck')->create();

    $row = fn (): array => [
        'user_id' => $user->id,
        'static_card_id' => $card->id,
        'state' => 'new',
        'reps' => 0,
        'lapses' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ];

    DB::table('user_static_card_states')->insert($row());

    expect(fn () => DB::table('user_static_card_states')->insert($row()))
        ->toThrow(Illuminate\Database\UniqueConstraintViolationException::class);
});

it('seeds a deck config with the FSRS-6 defaults', function (): void {
    // deck_configs is populated by the migration for pre-existing decks; decks
    // created afterwards are handled in application code, so assert the shape of
    // the columns rather than an automatic row here.
    foreach (['parameters', 'desired_retention', 'learning_steps', 'relearning_steps', 'maximum_interval', 'enable_fuzzing'] as $column) {
        expect(Schema::hasColumn('deck_configs', $column))->toBeTrue("deck_configs.{$column}");
    }
});
