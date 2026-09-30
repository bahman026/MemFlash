<?php

declare(strict_types=1);

use App\Fsrs\CardState;
use App\Fsrs\Fuzz\FixedFuzzSource;
use App\Fsrs\Fuzz\FuzzSource;
use App\Models\Card;
use App\Models\Deck;
use App\Models\ReviewLog;
use App\Models\StaticCard;
use App\Models\StaticDeck;
use App\Models\User;
use App\Models\UserStaticCardState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->app->bind(FuzzSource::class, fn () => new FixedFuzzSource(0.0));
    $this->user = User::factory()->create(['timezone' => 'UTC', 'rollover_hour' => 4]);
});

// -------------------------------------------------------------------------
// Bootstrap: everything needed to go offline
// -------------------------------------------------------------------------

it('returns decks, cards, state and presets in one request', function (): void {
    $deck = Deck::factory()->for($this->user)->create();
    Card::factory()->for($deck)->count(3)->create();

    $response = $this->actingAs($this->user)->getJson(route('sync.bootstrap'));

    $response->assertOk()
        ->assertJsonStructure([
            'synced_at',
            'user' => ['id', 'timezone', 'rollover_hour'],
            'decks' => [[
                'id', 'name', 'new_cards_per_day',
                'config' => ['parameters', 'desiredRetention', 'learningSteps', 'relearningSteps', 'maximumInterval', 'enableFuzzing'],
                'cards' => [['id', 'front', 'back', 'state', 'stability', 'difficulty', 'due', 'reps', 'lapses']],
            ]],
            'static_decks',
        ])
        ->assertJsonCount(3, 'decks.0.cards');

    // The preset must arrive in the shape the JavaScript scheduler consumes.
    expect($response->json('decks.0.config.parameters'))->toHaveCount(21)
        ->and($response->json('decks.0.config.learningSteps'))->toBe([60, 600]);
});

it('does not leak another user decks into the bootstrap', function (): void {
    Deck::factory()->for(User::factory())->create();

    $this->actingAs($this->user)
        ->getJson(route('sync.bootstrap'))
        ->assertOk()
        ->assertJsonCount(0, 'decks');
});

it('includes per-user static card state in the bootstrap', function (): void {
    $deck = StaticDeck::factory()->forLevel($this->user->level)->create();
    $card = StaticCard::factory()->for($deck, 'staticDeck')->create();

    UserStaticCardState::factory()->due(12.0, 4.0)->create([
        'user_id' => $this->user->id,
        'static_card_id' => $card->id,
    ]);

    $response = $this->actingAs($this->user)->getJson(route('sync.bootstrap'));

    $response->assertOk();

    // Cast explicitly: JSON gives back 12 for a whole-numbered double.
    expect((float) $response->json('static_decks.0.cards.0.stability'))->toBe(12.0)
        ->and($response->json('static_decks.0.cards.0.state'))->toBe('review');
});

it('reports unseen static cards as new rather than creating state', function (): void {
    $deck = StaticDeck::factory()->forLevel($this->user->level)->create();
    StaticCard::factory()->for($deck, 'staticDeck')->create();

    $response = $this->actingAs($this->user)->getJson(route('sync.bootstrap'));

    expect($response->json('static_decks.0.cards.0.state'))->toBe('new')
        ->and($response->json('static_decks.0.cards.0.stability'))->toBeNull()
        // A GET must not write.
        ->and(UserStaticCardState::count())->toBe(0);
});

// -------------------------------------------------------------------------
// Sync: replay with the server as authority
// -------------------------------------------------------------------------

it('replays a queue of offline reviews', function (): void {
    $deck = Deck::factory()->for($this->user)->create();
    $cards = Card::factory()->for($deck)->count(3)->create();

    $reviews = $cards->map(fn (Card $c, int $i): array => [
        'client_uuid' => (string) Str::uuid(),
        'type' => 'card',
        'card_id' => $c->id,
        'rating' => 3,
        'reviewed_at' => now()->subMinutes(10 - $i)->toIso8601String(),
        'review_duration_ms' => 1200,
    ])->all();

    $response = $this->actingAs($this->user)->postJson(route('sync.push'), ['reviews' => $reviews]);

    $response->assertOk()
        ->assertJsonCount(3, 'applied')
        ->assertJsonCount(0, 'rejected');

    expect(ReviewLog::count())->toBe(3)
        ->and(ReviewLog::where('synced_offline', true)->count())->toBe(3);

    // Each entry comes back with authoritative state the client can overwrite with.
    expect($response->json('applied.0'))->toHaveKeys(['client_uuid', 'state', 'stability', 'difficulty', 'due', 'reps']);
});

it('applies a replayed queue only once', function (): void {
    $deck = Deck::factory()->for($this->user)->create();
    $card = Card::factory()->for($deck)->create();

    $payload = ['reviews' => [[
        'client_uuid' => (string) Str::uuid(),
        'type' => 'card',
        'card_id' => $card->id,
        'rating' => 3,
        'reviewed_at' => now()->toIso8601String(),
    ]]];

    // The client never saw the first response and sends the queue again.
    foreach (range(1, 3) as $attempt) {
        $this->actingAs($this->user)->postJson(route('sync.push'), $payload)->assertOk();
    }

    expect(ReviewLog::count())->toBe(1)
        ->and($card->fresh()->reps)->toBe(1);
});

it('replays reviews oldest first regardless of queue order', function (): void {
    $deck = Deck::factory()->for($this->user)->create();
    $card = Card::factory()->for($deck)->create();

    $older = now()->subDays(3);
    $newer = now()->subDays(1);

    // Deliberately out of order in the payload.
    $this->actingAs($this->user)->postJson(route('sync.push'), ['reviews' => [
        ['client_uuid' => (string) Str::uuid(), 'type' => 'card', 'card_id' => $card->id, 'rating' => 3, 'reviewed_at' => $newer->toIso8601String()],
        ['client_uuid' => (string) Str::uuid(), 'type' => 'card', 'card_id' => $card->id, 'rating' => 3, 'reviewed_at' => $older->toIso8601String()],
    ]])->assertOk();

    $logs = ReviewLog::orderBy('id')->get();

    // Applied in chronological order: replaying backwards would compute each
    // interval from the wrong elapsed time.
    expect($logs)->toHaveCount(2)
        ->and($logs[0]->reviewed_at->lessThan($logs[1]->reviewed_at))->toBeTrue();
});

it('honours the original review timestamp instead of the sync time', function (): void {
    $deck = Deck::factory()->for($this->user)->create();
    $card = Card::factory()->for($deck)->create();
    $when = now()->subDays(2)->startOfMinute();

    $this->actingAs($this->user)->postJson(route('sync.push'), ['reviews' => [[
        'client_uuid' => (string) Str::uuid(),
        'type' => 'card',
        'card_id' => $card->id,
        'rating' => 3,
        'reviewed_at' => $when->toIso8601String(),
    ]]])->assertOk();

    expect(ReviewLog::sole()->reviewed_at->startOfMinute()->eq($when))->toBeTrue()
        ->and($card->fresh()->last_review->startOfMinute()->eq($when))->toBeTrue();
});

it('drops an offline review older than a later review of the same card', function (): void {
    $deck = Deck::factory()->for($this->user)->create();
    $card = Card::factory()->for($deck)->create();
    $monday = now()->subDays(3)->startOfMinute();
    $wednesday = now()->subDay()->startOfMinute();

    // Reviewed online on Wednesday...
    $this->actingAs($this->user)->postJson(route('sync.push'), ['reviews' => [[
        'client_uuid' => (string) Str::uuid(), 'type' => 'card', 'card_id' => $card->id,
        'rating' => 3, 'reviewed_at' => $wednesday->toIso8601String(),
    ]]])->assertOk();

    // ...then a phone syncs the rating it took offline on Monday.
    $this->actingAs($this->user)->postJson(route('sync.push'), ['reviews' => [[
        'client_uuid' => (string) Str::uuid(), 'type' => 'card', 'card_id' => $card->id,
        'rating' => 1, 'reviewed_at' => $monday->toIso8601String(),
    ]]])->assertOk()
        ->assertJsonCount(0, 'applied')
        ->assertJsonPath('rejected.0.reason', 'Superseded by a later review of this card.');

    expect($card->fresh()->last_review->startOfMinute()->eq($wednesday))->toBeTrue()
        ->and(ReviewLog::count())->toBe(1);
});

it('still accepts a retried review that was already applied', function (): void {
    $card = Card::factory()->for(Deck::factory()->for($this->user))->create();
    $first = ['client_uuid' => (string) Str::uuid(), 'type' => 'card', 'card_id' => $card->id, 'rating' => 3, 'reviewed_at' => now()->subHours(2)->toIso8601String()];
    $second = ['client_uuid' => (string) Str::uuid(), 'type' => 'card', 'card_id' => $card->id, 'rating' => 3, 'reviewed_at' => now()->subHour()->toIso8601String()];

    $this->actingAs($this->user)->postJson(route('sync.push'), ['reviews' => [$first, $second]])->assertOk();

    // The whole queue again, as after a lost response: the first entry is now older
    // than the card's last review, but it is a retry, not a stale review.
    $this->actingAs($this->user)->postJson(route('sync.push'), ['reviews' => [$first, $second]])
        ->assertOk()
        ->assertJsonCount(2, 'applied')
        ->assertJsonCount(0, 'rejected');

    expect(ReviewLog::count())->toBe(2);
});

it('does not let a device clock running ahead date a review in the future', function (): void {
    $card = Card::factory()->for(Deck::factory()->for($this->user))->create();

    $this->actingAs($this->user)->postJson(route('sync.push'), ['reviews' => [[
        'client_uuid' => (string) Str::uuid(), 'type' => 'card', 'card_id' => $card->id,
        'rating' => 3, 'reviewed_at' => now()->addDays(2)->toIso8601String(),
    ]]])->assertOk();

    expect($card->fresh()->last_review->lte(now()))->toBeTrue();
});

it('rejects a review for a card the caller does not own without failing the batch', function (): void {
    $mine = Card::factory()->for(Deck::factory()->for($this->user))->create();
    $theirs = Card::factory()->for(Deck::factory()->for(User::factory()))->create();

    $response = $this->actingAs($this->user)->postJson(route('sync.push'), ['reviews' => [
        ['client_uuid' => (string) Str::uuid(), 'type' => 'card', 'card_id' => $mine->id, 'rating' => 3, 'reviewed_at' => now()->toIso8601String()],
        ['client_uuid' => (string) Str::uuid(), 'type' => 'card', 'card_id' => $theirs->id, 'rating' => 3, 'reviewed_at' => now()->toIso8601String()],
    ]]);

    $response->assertOk()
        ->assertJsonCount(1, 'applied')
        ->assertJsonCount(1, 'rejected');

    expect($mine->fresh()->reps)->toBe(1)
        ->and($theirs->fresh()->reps)->toBe(0)
        ->and(ReviewLog::count())->toBe(1);
});

it('reports a missing card as rejected rather than erroring', function (): void {
    $response = $this->actingAs($this->user)->postJson(route('sync.push'), ['reviews' => [
        ['client_uuid' => (string) Str::uuid(), 'type' => 'card', 'card_id' => 999999, 'rating' => 3, 'reviewed_at' => now()->toIso8601String()],
    ]]);

    $response->assertOk()->assertJsonCount(1, 'rejected');
});

it('replays static card reviews against per-user state', function (): void {
    $deck = StaticDeck::factory()->create();
    $card = StaticCard::factory()->for($deck, 'staticDeck')->create();

    $this->actingAs($this->user)->postJson(route('sync.push'), ['reviews' => [[
        'client_uuid' => (string) Str::uuid(),
        'type' => 'static_card',
        'card_id' => $card->id,
        'rating' => 4,
        'reviewed_at' => now()->toIso8601String(),
    ]]])->assertOk()->assertJsonCount(1, 'applied');

    $state = UserStaticCardState::where('user_id', $this->user->id)->sole();

    expect($state->reps)->toBe(1)
        ->and(round((float) $state->stability, 4))->toBe(8.2956);   // S0(Easy)
});

it('validates the queue shape', function (array $review): void {
    $this->actingAs($this->user)
        ->postJson(route('sync.push'), ['reviews' => [$review]])
        ->assertStatus(422);
})->with([
    'bad rating' => [['client_uuid' => '11111111-1111-4111-8111-111111111111', 'type' => 'card', 'card_id' => 1, 'rating' => 9, 'reviewed_at' => '2026-01-01T00:00:00Z']],
    'bad type' => [['client_uuid' => '11111111-1111-4111-8111-111111111111', 'type' => 'nope', 'card_id' => 1, 'rating' => 3, 'reviewed_at' => '2026-01-01T00:00:00Z']],
    'missing uuid' => [['type' => 'card', 'card_id' => 1, 'rating' => 3, 'reviewed_at' => '2026-01-01T00:00:00Z']],
    'bad date' => [['client_uuid' => '11111111-1111-4111-8111-111111111111', 'type' => 'card', 'card_id' => 1, 'rating' => 3, 'reviewed_at' => 'not-a-date']],
]);

it('requires authentication for every sync route', function (string $method, string $route): void {
    $this->{$method}(route($route))->assertRedirect(route('login.page'));
})->with([
    ['get', 'sync.bootstrap'],
    ['post', 'sync.push'],
    ['get', 'sync.status'],
]);

it('reports how many reviews have been logged', function (): void {
    $deck = Deck::factory()->for($this->user)->create();
    $card = Card::factory()->for($deck)->create();

    $this->actingAs($this->user)->postJson(route('study.update-card', $card), ['rating' => 3])->assertOk();

    $this->actingAs($this->user)
        ->getJson(route('sync.status'))
        ->assertOk()
        ->assertJson(['logged_reviews' => 1]);
});

// -------------------------------------------------------------------------
// The server overrides the client
// -------------------------------------------------------------------------

it('returns server computed state that overrides whatever the client calculated', function (): void {
    $deck = Deck::factory()->for($this->user)->create();
    $deck->configOrDefault()->update(['enable_fuzzing' => false, 'learning_steps' => []]);

    $card = Card::factory()->for($deck)->create([
        'state' => CardState::Review,
        'stability' => 10.0,
        'difficulty' => 5.0,
        'last_review' => now()->subDays(10),
        'reps' => 5,
    ]);

    $response = $this->actingAs($this->user)->postJson(route('sync.push'), ['reviews' => [[
        'client_uuid' => (string) Str::uuid(),
        'type' => 'card',
        'card_id' => $card->id,
        'rating' => 3,
        'reviewed_at' => now()->toIso8601String(),
    ]]]);

    // Reference vector 4.4 Good: S 32.0267, D 4.9902.
    expect(round((float) $response->json('applied.0.stability'), 4))->toBe(32.0267)
        ->and(round((float) $response->json('applied.0.difficulty'), 4))->toBe(4.9902)
        ->and($response->json('applied.0.state'))->toBe('review');
});

it('downloads a single deck for the study screen to refresh', function (): void {
    $deck = Deck::factory()->for($this->user)->create();
    Card::factory()->for($deck)->count(3)->create();
    Deck::factory()->for($this->user)->create();

    $this->actingAs($this->user)
        ->getJson(route('sync.bootstrap', ['deck' => "card:{$deck->id}"]))
        ->assertOk()
        ->assertJsonCount(1, 'decks')
        ->assertJsonPath('decks.0.id', $deck->id)
        ->assertJsonCount(3, 'decks.0.cards')
        ->assertJsonCount(0, 'static_decks');
});

it('will not download a single deck the caller does not own', function (): void {
    $theirs = Deck::factory()->for(User::factory())->create();

    $this->actingAs($this->user)
        ->getJson(route('sync.bootstrap', ['deck' => "card:{$theirs->id}"]))
        ->assertNotFound();

    $this->actingAs($this->user)
        ->getJson(route('sync.bootstrap', ['deck' => 'nonsense']))
        ->assertStatus(422);
});

it('downloads any single lesson, not only those at the user level', function (): void {
    $lesson = StaticDeck::factory()->create();
    StaticCard::factory()->for($lesson, 'staticDeck')->count(2)->create();

    $this->actingAs($this->user)
        ->getJson(route('sync.bootstrap', ['deck' => "static_card:{$lesson->id}"]))
        ->assertOk()
        ->assertJsonCount(0, 'decks')
        ->assertJsonPath('static_decks.0.id', $lesson->id)
        ->assertJsonCount(2, 'static_decks.0.cards');
});

it('tells the offline client how many new cards each deck started today', function (): void {
    $deck = Deck::factory()->for($this->user)->create(['new_cards_per_day' => 5]);
    $cards = Card::factory()->for($deck)->count(4)->create();

    $this->actingAs($this->user)->postJson(route('sync.push'), ['reviews' => [
        ['client_uuid' => (string) Str::uuid(), 'type' => 'card', 'card_id' => $cards[0]->id, 'rating' => 3, 'reviewed_at' => now()->toIso8601String()],
        ['client_uuid' => (string) Str::uuid(), 'type' => 'card', 'card_id' => $cards[1]->id, 'rating' => 3, 'reviewed_at' => now()->toIso8601String()],
    ]])->assertOk();

    $this->actingAs($this->user)
        ->getJson(route('sync.bootstrap', ['deck' => "card:{$deck->id}"]))
        ->assertOk()
        ->assertJsonPath('decks.0.new_cards_per_day', 5)
        ->assertJsonPath('decks.0.new_cards_today', 2)
        ->assertJsonStructure(['user' => ['study_day_started_at']]);
});

it('moves lesson progress when offline reviews are synced, once per card', function (): void {
    $lesson = StaticDeck::factory()->create();
    $cards = StaticCard::factory()->for($lesson, 'staticDeck')->count(5)->create();
    $queue = [
        ['client_uuid' => (string) Str::uuid(), 'type' => 'static_card', 'card_id' => $cards[0]->id, 'rating' => 1, 'reviewed_at' => now()->subMinutes(3)->toIso8601String()],
        ['client_uuid' => (string) Str::uuid(), 'type' => 'static_card', 'card_id' => $cards[0]->id, 'rating' => 3, 'reviewed_at' => now()->subMinutes(2)->toIso8601String()],
        ['client_uuid' => (string) Str::uuid(), 'type' => 'static_card', 'card_id' => $cards[1]->id, 'rating' => 3, 'reviewed_at' => now()->subMinute()->toIso8601String()],
    ];

    $this->actingAs($this->user)->postJson(route('sync.push'), ['reviews' => $queue])->assertOk();
    // A retried sync must not count the same first reviews again.
    $this->actingAs($this->user)->postJson(route('sync.push'), ['reviews' => $queue])->assertOk();

    expect(App\Models\UserStaticDeckProgress::where('user_id', $this->user->id)->sole()->cards_studied)->toBe(2);
});
