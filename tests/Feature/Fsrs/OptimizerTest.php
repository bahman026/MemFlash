<?php

declare(strict_types=1);

use App\Fsrs\Optimizer\Evaluator;
use App\Fsrs\Optimizer\Optimizer;
use App\Fsrs\Optimizer\TrainingSet;
use App\Fsrs\Parameters;
use App\Jobs\OptimizeFsrsParameters;
use App\Models\Card;
use App\Models\Deck;
use App\Models\ReviewLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->deck = Deck::factory()->for($this->user)->create();
});

/**
 * Write a synthetic review history straight into review_logs.
 *
 * Generated rather than produced by studying, because the optimizer needs hundreds
 * of reviews and the point is to exercise the fitting, not the scheduler.
 *
 * @param  list<array{rating: int, elapsed: int}>  $reviews
 */
function logHistory(User $user, Card $card, array $reviews): void
{
    $at = now()->subYear();

    foreach ($reviews as $review) {
        $at = $at->copy()->addDays(max(1, $review['elapsed']));

        ReviewLog::create([
            'user_id' => $user->id,
            'reviewable_type' => Card::class,
            'reviewable_id' => $card->id,
            'rating' => $review['rating'],
            'state_before' => 'review',
            'elapsed_days' => $review['elapsed'],
            'retrievability_before' => 0.9,
            'stability_after' => 10.0,
            'difficulty_after' => 5.0,
            'scheduled_days' => $review['elapsed'],
            'reviewed_at' => $at,
            'created_at' => $at,
        ]);
    }
}

/**
 * A learner who mostly succeeds, with lapses concentrated on long intervals --
 * the pattern FSRS is meant to capture.
 */
function seedRealisticHistory(User $user, Deck $deck, int $cards = 60, int $perCard = 9): void
{
    mt_srand(20260804); // deterministic so the assertions are stable

    for ($c = 0; $c < $cards; $c++) {
        $card = Card::factory()->for($deck)->create();
        $reviews = [['rating' => 3, 'elapsed' => 0]];
        $interval = 1;

        for ($r = 1; $r < $perCard; $r++) {
            // Longer gaps lapse more often.
            $lapseChance = min(60, 5 + $interval);
            $lapsed = mt_rand(1, 100) <= $lapseChance;

            $reviews[] = ['rating' => $lapsed ? 1 : 3, 'elapsed' => $interval];
            $interval = $lapsed ? 1 : max(1, (int) round($interval * 2.2));
        }

        logHistory($user, $card, $reviews);
    }
}

// -------------------------------------------------------------------------
// Training set construction
// -------------------------------------------------------------------------

it('keeps only the first review of a card on any given day', function (): void {
    $card = Card::factory()->for($this->deck)->create();
    $at = now()->subDays(5);

    foreach ([1, 2, 3] as $i) {
        ReviewLog::create([
            'user_id' => $this->user->id,
            'reviewable_type' => Card::class,
            'reviewable_id' => $card->id,
            'rating' => 3,
            'state_before' => 'review',
            'elapsed_days' => 0,
            'retrievability_before' => 0.9,
            'stability_after' => 5.0,
            'difficulty_after' => 5.0,
            'scheduled_days' => 0,
            // Same calendar day, three times.
            'reviewed_at' => $at->copy()->addHours($i),
            'created_at' => $at,
        ]);
    }

    // Then one the next day, so the sequence is long enough to be kept.
    ReviewLog::create([
        'user_id' => $this->user->id,
        'reviewable_type' => Card::class,
        'reviewable_id' => $card->id,
        'rating' => 3,
        'state_before' => 'review',
        'elapsed_days' => 1,
        'retrievability_before' => 0.9,
        'stability_after' => 5.0,
        'difficulty_after' => 5.0,
        'scheduled_days' => 1,
        'reviewed_at' => $at->copy()->addDay(),
        'created_at' => $at,
    ]);

    $set = TrainingSet::forDeck($this->user->id, $this->deck->id);

    expect($set->cardCount)->toBe(1)
        ->and($set->reviewCount)->toBe(2); // three same-day reviews collapse to one
});

it('discards cards with a single review', function (): void {
    $card = Card::factory()->for($this->deck)->create();
    logHistory($this->user, $card, [['rating' => 3, 'elapsed' => 0]]);

    $set = TrainingSet::forDeck($this->user->id, $this->deck->id);

    // One review cannot be predicted from a prior state, so it carries no signal.
    expect($set->isEmpty())->toBeTrue()
        ->and($set->predictableCount())->toBe(0);
});

it('excludes each card first review from the predictable count', function (): void {
    foreach (range(1, 3) as $i) {
        logHistory($this->user, Card::factory()->for($this->deck)->create(), [
            ['rating' => 3, 'elapsed' => 0],
            ['rating' => 3, 'elapsed' => 2],
            ['rating' => 3, 'elapsed' => 6],
        ]);
    }

    $set = TrainingSet::forDeck($this->user->id, $this->deck->id);

    expect($set->reviewCount)->toBe(9)
        ->and($set->cardCount)->toBe(3)
        ->and($set->predictableCount())->toBe(6);
});

it('does not mix one user history into another', function (): void {
    $other = User::factory()->create();
    $otherDeck = Deck::factory()->for($other)->create();

    logHistory($other, Card::factory()->for($otherDeck)->create(), [
        ['rating' => 3, 'elapsed' => 0],
        ['rating' => 3, 'elapsed' => 3],
    ]);

    expect(TrainingSet::forDeck($this->user->id, $this->deck->id)->isEmpty())->toBeTrue()
        ->and(TrainingSet::forDeck($other->id, $otherDeck->id)->isEmpty())->toBeFalse();
});

// -------------------------------------------------------------------------
// Evaluator
// -------------------------------------------------------------------------

it('scores a log loss and an RMSE without blowing up', function (): void {
    seedRealisticHistory($this->user, $this->deck, cards: 20, perCard: 6);

    $evaluator = new Evaluator(TrainingSet::forDeck($this->user->id, $this->deck->id));
    $loss = $evaluator->logLoss(new Parameters);

    expect($loss)->toBeGreaterThan(0.0)
        ->and(is_finite($loss))->toBeTrue()
        ->and($evaluator->rmse(new Parameters))->toBeGreaterThanOrEqual(0.0)
        ->and($evaluator->measuredRetention())->toBeGreaterThan(0.0)
        ->and($evaluator->measuredRetention())->toBeLessThanOrEqual(1.0);
});

it('reports an infinite loss for an empty set so it can never look like an improvement', function (): void {
    $evaluator = new Evaluator(TrainingSet::forDeck($this->user->id, $this->deck->id));

    expect($evaluator->logLoss(new Parameters))->toBe(INF);
});

it('never reads the stored retrievability, since it came from the old parameters', function (): void {
    seedRealisticHistory($this->user, $this->deck, cards: 15, perCard: 6);

    $set = TrainingSet::forDeck($this->user->id, $this->deck->id);
    $before = (new Evaluator($set))->logLoss(new Parameters);

    // Corrupt every stored R. The loss must not move.
    ReviewLog::query()->update(['retrievability_before' => 0.01]);

    $after = (new Evaluator(TrainingSet::forDeck($this->user->id, $this->deck->id)))->logLoss(new Parameters);

    expect($after)->toBe($before);
});

// -------------------------------------------------------------------------
// Optimizer
// -------------------------------------------------------------------------

it('refuses to optimize below the review threshold', function (): void {
    seedRealisticHistory($this->user, $this->deck, cards: 5, perCard: 5);

    $optimizer = new Optimizer(TrainingSet::forDeck($this->user->id, $this->deck->id));

    expect($optimizer->hasEnoughData())->toBeFalse()
        ->and(fn () => $optimizer->run())->toThrow(RuntimeException::class, 'Not enough review history');
});

it('reduces the log loss on a real history', function (): void {
    seedRealisticHistory($this->user, $this->deck, cards: 70, perCard: 9);

    $set = TrainingSet::forDeck($this->user->id, $this->deck->id);
    expect($set->predictableCount())->toBeGreaterThanOrEqual(Optimizer::MINIMUM_REVIEWS);

    $result = (new Optimizer($set))->run();

    expect($result['final_loss'])->toBeLessThanOrEqual($result['initial_loss'])
        ->and($result['iterations'])->toBeGreaterThan(0)
        ->and($result['parameters'])->toHaveCount(21);
})->group('slow');

it('keeps every fitted parameter inside its bounds', function (): void {
    seedRealisticHistory($this->user, $this->deck, cards: 70, perCard: 9);

    $result = (new Optimizer(TrainingSet::forDeck($this->user->id, $this->deck->id)))->run();
    $w = $result['parameters'];

    // The clamps that matter: a hard penalty outside (0,1) or a decay outside
    // [0.1,0.8] would produce nonsensical schedules.
    expect($w[15])->toBeGreaterThan(0.0)->toBeLessThan(1.0)
        ->and($w[16])->toBeGreaterThanOrEqual(1.0)->toBeLessThanOrEqual(6.0)
        ->and($w[20])->toBeGreaterThanOrEqual(0.1)->toBeLessThanOrEqual(0.8);

    // And the result must be usable by the scheduler.
    expect(fn () => new Parameters($w))->not->toThrow(Exception::class);
})->group('slow');

it('falls back to the defaults rather than shipping a worse fit', function (): void {
    seedRealisticHistory($this->user, $this->deck, cards: 70, perCard: 9);

    $result = (new Optimizer(TrainingSet::forDeck($this->user->id, $this->deck->id)))->run();

    if (! $result['improved']) {
        expect($result['parameters'])->toBe(Parameters::DEFAULTS);
    } else {
        expect($result['final_loss'])->toBeLessThan($result['initial_loss']);
    }
})->group('slow');

// -------------------------------------------------------------------------
// Job
// -------------------------------------------------------------------------

it('is queued rather than run inline', function (): void {
    Queue::fake();

    dispatch(new OptimizeFsrsParameters($this->user->id, 'personal', $this->deck->id));

    Queue::assertPushed(OptimizeFsrsParameters::class);
});

it('is unique per deck so two runs cannot race on the same config', function (): void {
    $job = new OptimizeFsrsParameters($this->user->id, 'personal', $this->deck->id);

    expect($job)->toBeInstanceOf(Illuminate\Contracts\Queue\ShouldBeUnique::class)
        ->and($job->uniqueId())->toBe("{$this->user->id}:personal:{$this->deck->id}");
});

it('leaves the config untouched when there is not enough history', function (): void {
    seedRealisticHistory($this->user, $this->deck, cards: 3, perCard: 4);

    (new OptimizeFsrsParameters($this->user->id, 'personal', $this->deck->id))->handle();

    $config = $this->deck->configOrDefault()->fresh();

    expect($config->optimized_at)->toBeNull()
        ->and($config->parameters)->toBe(Parameters::DEFAULTS);
});

it('records the fit on the deck config', function (): void {
    seedRealisticHistory($this->user, $this->deck, cards: 70, perCard: 9);

    (new OptimizeFsrsParameters($this->user->id, 'personal', $this->deck->id))->handle();

    $config = $this->deck->configOrDefault()->fresh();

    expect($config->optimized_at)->not->toBeNull()
        ->and($config->optimized_review_count)->toBeGreaterThanOrEqual(Optimizer::MINIMUM_REVIEWS)
        ->and($config->parameters)->toHaveCount(21);
})->group('slow');

it('cannot fit one user parameters from another user history', function (): void {
    $other = User::factory()->create();
    $theirDeck = Deck::factory()->for($other)->create();
    seedRealisticHistory($other, $theirDeck, cards: 70, perCard: 9);

    $before = $theirDeck->configOrDefault()->fresh();

    // Someone else's deck id with this user's id. The training set is scoped by
    // user_id, so no history is found and the job bails before it can write --
    // the ownership check in persist() is a second line of defence, not the first.
    (new OptimizeFsrsParameters($this->user->id, 'personal', $theirDeck->id))->handle();

    $after = $theirDeck->configOrDefault()->fresh();

    expect($after->optimized_at)->toBeNull()
        ->and($after->parameters)->toBe($before->parameters)
        ->and($after->updated_at->eq($before->updated_at))->toBeTrue();
});

it('still refuses to write to a deck the user does not own', function (): void {
    // Directly exercising the guard in persist(), which the scoped training set
    // normally prevents from ever being reached.
    $other = User::factory()->create();
    $theirDeck = Deck::factory()->for($other)->create();

    $job = new OptimizeFsrsParameters($this->user->id, 'personal', $theirDeck->id);
    $persist = new ReflectionMethod($job, 'persist');

    expect(fn () => $persist->invoke($job, $this->user, [
        'parameters' => Parameters::DEFAULTS,
        'reviews' => 500,
    ]))->toThrow(RuntimeException::class, 'does not belong to user');
});
