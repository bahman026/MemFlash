<?php

declare(strict_types=1);

use App\Fsrs\CardState;
use App\Fsrs\Fuzz\FixedFuzzSource;
use App\Fsrs\Fuzz\FuzzSource;
use App\Fsrs\Rating;
use App\Models\Card;
use App\Models\Deck;
use App\Models\ReviewLog;
use App\Models\StaticCard;
use App\Models\StaticDeck;
use App\Models\User;
use App\Models\UserStaticCardState;
use App\Services\ReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Deterministic scheduling: the reference vectors all assume fuzz is off, and
    // 0.0 puts every fuzzed interval at the low end of its window.
    $this->app->bind(FuzzSource::class, fn () => new FixedFuzzSource(0.0));

    $this->reviews = app(ReviewService::class);
    $this->user = User::factory()->create(['timezone' => 'UTC', 'rollover_hour' => 4]);
});

// -------------------------------------------------------------------------
// Personal cards
// -------------------------------------------------------------------------

it('takes a new card into learning and logs the review', function (): void {
    $deck = Deck::factory()->for($this->user)->create();
    $card = Card::factory()->for($deck)->create();

    $outcome = $this->reviews->reviewCard($this->user, $card, Rating::Good, durationMs: 1450);

    $card->refresh();

    expect($card->state)->toBe(CardState::Learning)
        ->and($card->step)->toBe(1) // Good on a new card moves past the first step
        ->and(round((float) $card->stability, 4))->toBe(2.3065)   // S0(Good)
        ->and(round((float) $card->difficulty, 4))->toBe(2.1181)  // D0(Good)
        ->and($card->reps)->toBe(1)
        ->and($card->lapses)->toBe(0)
        ->and($card->due->isAfter(now()))->toBeTrue();

    $log = ReviewLog::sole();

    expect($log->user_id)->toBe($this->user->id)
        ->and($log->reviewable_id)->toBe($card->id)
        ->and($log->reviewable_type)->toBe(Card::class)
        ->and($log->rating)->toBe(Rating::Good)
        ->and($log->state_before)->toBe(CardState::New)
        ->and($log->elapsed_days)->toBe(0)
        ->and($log->retrievability_before)->toBe(1.0)
        ->and($log->review_duration_ms)->toBe(1450)
        ->and($outcome->stability)->toBe((float) $card->stability);
});

it('writes one log row per review and never updates an old one', function (): void {
    $deck = Deck::factory()->for($this->user)->create();
    $card = Card::factory()->for($deck)->create();

    foreach ([Rating::Good, Rating::Good, Rating::Again, Rating::Hard] as $rating) {
        $this->reviews->reviewCard($this->user, $card, $rating);
    }

    expect(ReviewLog::count())->toBe(4)
        ->and(ReviewLog::pluck('rating')->map(fn ($r) => $r->value)->all())->toBe([3, 3, 1, 2]);
});

it('reproduces the reference vector through the full stack', function (): void {
    // Vector 4.4: S = 10, D = 5, elapsed 10 days so R = 0.9 exactly.
    $deck = Deck::factory()->for($this->user)->create();
    $deck->configOrDefault()->update(['enable_fuzzing' => false, 'learning_steps' => []]);

    $card = Card::factory()->for($deck)->create([
        'state' => CardState::Review,
        'stability' => 10.0,
        'difficulty' => 5.0,
        'last_review' => now()->subDays(10),
        'due' => now(),
        'reps' => 5,
    ]);

    $outcome = $this->reviews->reviewCard($this->user, $card, Rating::Good);

    expect($outcome->elapsedDays)->toBe(10)
        ->and(round($outcome->retrievabilityBefore, 10))->toBe(0.9)
        ->and(round($outcome->difficulty, 4))->toBe(4.9960)
        ->and(round($outcome->stability, 4))->toBe(32.0414)
        ->and($outcome->scheduledDays)->toBe(32);
});

it('sends a lapsed review card into relearning', function (): void {
    $deck = Deck::factory()->for($this->user)->create();
    $card = Card::factory()->for($deck)->create([
        'state' => CardState::Review,
        'stability' => 10.0,
        'difficulty' => 5.0,
        'last_review' => now()->subDays(10),
        'due' => now(),
        'reps' => 5,
    ]);

    $this->reviews->reviewCard($this->user, $card, Rating::Again);
    $card->refresh();

    expect($card->state)->toBe(CardState::Relearning)
        ->and($card->step)->toBe(0)
        ->and($card->lapses)->toBe(1)
        ->and(round((float) $card->stability, 4))->toBe(1.3489)
        ->and(round((float) $card->difficulty, 4))->toBe(8.3475);
});

it('never stores retrievability but derives it on read', function (): void {
    $deck = Deck::factory()->for($this->user)->create();
    $card = Card::factory()->for($deck)->create([
        'state' => CardState::Review,
        'stability' => 10.0,
        'difficulty' => 5.0,
        'last_review' => now()->subDays(10),
    ]);

    expect(Schema::hasColumn('cards', 'retrievability'))->toBeFalse()
        ->and(round($card->retrievability(), 4))->toBe(0.9);
});

it('previews an interval for every rating without recording anything', function (): void {
    $deck = Deck::factory()->for($this->user)->create();
    $deck->configOrDefault()->update(['enable_fuzzing' => false, 'learning_steps' => []]);

    $card = Card::factory()->for($deck)->create([
        'state' => CardState::Review,
        'stability' => 10.0,
        'difficulty' => 5.0,
        'last_review' => now()->subDays(10),
        'reps' => 5,
    ]);

    $intervals = $this->reviews->previewIntervals($this->user, $card);

    // Again goes to relearning in 10 minutes, so it reports seconds, not days.
    expect($intervals[Rating::Again->value])->toBe(['state' => 'relearning', 'days' => 0, 'seconds' => 600])
        // Vector 4.4: Hard 20, Good 32, Easy 63 days.
        ->and($intervals[Rating::Hard->value]['days'])->toBe(20)
        ->and($intervals[Rating::Good->value]['days'])->toBe(32)
        ->and($intervals[Rating::Easy->value]['days'])->toBe(63)
        ->and($intervals[Rating::Good->value]['state'])->toBe('review')
        // Previewing must not record or mutate anything.
        ->and(ReviewLog::count())->toBe(0)
        ->and($card->fresh()->stability)->toBe(10.0)
        ->and($card->fresh()->reps)->toBe(5);
});

// -------------------------------------------------------------------------
// Offline replay safety
// -------------------------------------------------------------------------

it('applies an offline review only once even if the sync is replayed', function (): void {
    $deck = Deck::factory()->for($this->user)->create();
    $card = Card::factory()->for($deck)->create();
    $uuid = (string) Str::uuid();

    $first = $this->reviews->reviewCard($this->user, $card, Rating::Good, clientUuid: $uuid, offline: true);
    $stateAfterFirst = $card->fresh()->only(['state', 'stability', 'difficulty', 'reps']);

    // The client never got the response and retries the same queued review.
    $second = $this->reviews->reviewCard($this->user, $card, Rating::Good, clientUuid: $uuid, offline: true);

    expect(ReviewLog::count())->toBe(1)
        ->and($card->fresh()->only(['state', 'stability', 'difficulty', 'reps']))->toBe($stateAfterFirst)
        ->and($card->fresh()->reps)->toBe(1)
        ->and(round($second->stability, 4))->toBe(round($first->stability, 4));
});

it('marks offline reviews so they can be told apart later', function (): void {
    $deck = Deck::factory()->for($this->user)->create();
    $card = Card::factory()->for($deck)->create();

    $this->reviews->reviewCard($this->user, $card, Rating::Good, clientUuid: (string) Str::uuid(), offline: true);

    expect(ReviewLog::sole()->synced_offline)->toBeTrue();
});

// -------------------------------------------------------------------------
// Static cards: per-user state
// -------------------------------------------------------------------------

it('keeps static card scheduling separate per user', function (): void {
    $deck = StaticDeck::factory()->create();
    $card = StaticCard::factory()->for($deck, 'staticDeck')->create();
    $other = User::factory()->create();

    $this->reviews->reviewStaticCard($this->user, $card, Rating::Easy);

    $mine = UserStaticCardState::where('user_id', $this->user->id)->where('static_card_id', $card->id)->first();
    $theirs = UserStaticCardState::where('user_id', $other->id)->where('static_card_id', $card->id)->first();

    expect($mine)->not->toBeNull()
        ->and($mine->reps)->toBe(1)
        ->and(round((float) $mine->stability, 4))->toBe(8.2956)   // S0(Easy)
        // The other user has no state at all: studying did not touch them.
        ->and($theirs)->toBeNull();
});

it('does not let one user reset another user progress', function (): void {
    $deck = StaticDeck::factory()->create();
    $card = StaticCard::factory()->for($deck, 'staticDeck')->create();
    $other = User::factory()->create();

    $this->reviews->reviewStaticCard($this->user, $card, Rating::Good);
    $this->reviews->reviewStaticCard($other, $card, Rating::Good);

    $deck->resetLearningProgressFor($this->user);

    $mine = UserStaticCardState::where('user_id', $this->user->id)->sole();
    $theirs = UserStaticCardState::where('user_id', $other->id)->sole();

    expect($mine->state)->toBe(CardState::New)
        ->and($mine->stability)->toBeNull()
        ->and($mine->reps)->toBe(0)
        // Untouched.
        ->and($theirs->state)->toBe(CardState::Learning)
        ->and($theirs->reps)->toBe(1);
});

it('logs static reviews against the shared card but with the acting user', function (): void {
    $deck = StaticDeck::factory()->create();
    $card = StaticCard::factory()->for($deck, 'staticDeck')->create();

    $this->reviews->reviewStaticCard($this->user, $card, Rating::Good);

    $log = ReviewLog::sole();

    expect($log->reviewable_type)->toBe(StaticCard::class)
        ->and($log->reviewable_id)->toBe($card->id)
        ->and($log->user_id)->toBe($this->user->id);
});

it('keeps review history when a card is forgotten', function (): void {
    $deck = Deck::factory()->for($this->user)->create();
    $card = Card::factory()->for($deck)->create();

    $this->reviews->reviewCard($this->user, $card, Rating::Good);
    $this->reviews->forget($this->user, $card);

    $card->refresh();

    expect($card->state)->toBe(CardState::New)
        ->and($card->stability)->toBeNull()
        ->and($card->reps)->toBe(0)
        // The log is append-only; resetting a card must not erase its history.
        ->and(ReviewLog::count())->toBe(1);
});

it('uses the deck preset rather than global defaults', function (): void {
    $deck = Deck::factory()->for($this->user)->create();
    $deck->configOrDefault()->update([
        'desired_retention' => 0.97,
        'enable_fuzzing' => false,
        'learning_steps' => [],
    ]);

    $card = Card::factory()->for($deck)->create([
        'state' => CardState::Review,
        'stability' => 100.0,
        'difficulty' => 5.0,
        'last_review' => now()->subDays(100),
        'reps' => 5,
    ]);

    $intervals = $this->reviews->previewIntervals($this->user, $card);

    // At 0.97 desired retention the interval is ~0.223 x S, far shorter than at 0.90.
    expect($intervals[Rating::Good->value]['days'])->toBeLessThan(100);
});

it('computes retrievability using the deck own parameters, not the FSRS-6 defaults', function (): void {
    $deck = Deck::factory()->for($this->user)->create();
    $custom = \App\Fsrs\Parameters::DEFAULTS;
    $custom[20] = 0.35; // a very different decay from the default 0.1542
    $deck->configOrDefault()->update(['parameters' => $custom]);

    $card = Card::factory()->for($deck)->create([
        'state' => CardState::Review,
        'stability' => 10.0,
        'difficulty' => 5.0,
        'last_review' => now()->subDays(30),
        'reps' => 5,
    ]);

    $correct = $this->reviews->retrievabilityOf($this->user, $card);
    $wrong = $card->retrievability(); // the deck-blind approximation, for contrast

    // Confirmed by direct computation: default weights give ~0.809, the deck's
    // optimized weights give ~0.777 for this elapsed time and stability.
    expect(round($correct, 3))->toBe(0.777)
        ->and(round($wrong, 3))->toBe(0.809)
        ->and($correct)->not->toEqual($wrong);
});

it('computes retrievability using rollover-aware elapsed days, not a naive diff', function (): void {
    $this->user->update(['rollover_hour' => 4, 'timezone' => 'UTC']);
    $deck = Deck::factory()->for($this->user)->create();

    // last_review at 23:00, "now" at 02:00 the next day: a naive day diff already
    // sees a fractional day elapsed; the 4am rollover says this is still the same
    // day, so retrievability should not have decayed from 1.0 yet.
    $card = Card::factory()->for($deck)->create([
        'state' => CardState::Review,
        'stability' => 10.0,
        'difficulty' => 5.0,
        'last_review' => now()->setTime(23, 0),
        'reps' => 5,
    ]);

    $at = now()->addDay()->setTime(2, 0)->toDateTimeImmutable();

    expect($this->reviews->retrievabilityOf($this->user, $card, now: $at))->toBe(1.0);
});

it('stays read-only when the caller supplies its own snapshot for an unseen card', function (): void {
    // Mirrors how StaticDeckController::getCards() calls this: for a card with no
    // saved state, it builds an unsaved UserStaticCardState instance itself rather
    // than letting stateFor() firstOrCreate one, so rendering a queue (a GET)
    // cannot write. Passing that same unsaved instance's snapshot through here
    // must not write either.
    $deck = StaticDeck::factory()->create();
    $card = StaticCard::factory()->for($deck, 'staticDeck')->create();
    $unsaved = new UserStaticCardState(['user_id' => $this->user->id, 'static_card_id' => $card->id]);

    $value = $this->reviews->retrievabilityOf($this->user, $card, snapshot: $unsaved->toSnapshot());

    expect($value)->toBe(1.0)
        ->and(UserStaticCardState::count())->toBe(0);
});

it('creates state on demand when no snapshot is supplied, like previewIntervals does', function (): void {
    // Documents the actual contract: calling without a snapshot is a convenience
    // that resolves (and persists) the card's real state, matching
    // previewIntervals()'s identical behaviour. Callers that must stay read-only
    // -- rendering a queue of many cards -- pass a snapshot instead.
    $deck = StaticDeck::factory()->create();
    $card = StaticCard::factory()->for($deck, 'staticDeck')->create();

    $this->reviews->retrievabilityOf($this->user, $card);

    expect(UserStaticCardState::where('user_id', $this->user->id)->where('static_card_id', $card->id)->exists())
        ->toBeTrue();
});
