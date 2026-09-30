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
    $this->user = User::factory()->create();
});

// -------------------------------------------------------------------------
// Queue endpoint
// -------------------------------------------------------------------------

it('returns the due queue with an interval preview per rating', function (): void {
    $deck = Deck::factory()->for($this->user)->create(['new_cards_per_day' => 5]);
    Card::factory()->for($deck)->count(3)->create();
    Card::factory()->for($deck)->notDue()->count(2)->create();

    $response = $this->actingAs($this->user)->getJson(route('study.cards', $deck));

    $response->assertOk()
        ->assertJsonCount(3, 'cards')
        ->assertJsonStructure([
            'cards' => [['id', 'front', 'back', 'state', 'stability', 'difficulty', 'retrievability', 'due', 'intervals']],
            'deck' => ['id', 'name', 'total_cards', 'new_cards_per_day', 'cards_loaded', 'desired_retention'],
        ]);

    // Four ratings, each with a state, a day count and a second count.
    // json() decodes the object's numeric keys back to PHP integers.
    $intervals = $response->json('cards.0.intervals');
    expect(array_keys($intervals))->toBe([1, 2, 3, 4]);
    foreach ($intervals as $preview) {
        expect($preview)->toHaveKeys(['state', 'days', 'seconds']);
    }
});

it('excludes suspended cards from the queue', function (): void {
    $deck = Deck::factory()->for($this->user)->create();
    Card::factory()->for($deck)->create();
    Card::factory()->for($deck)->suspended()->create();

    $this->actingAs($this->user)
        ->getJson(route('study.cards', $deck))
        ->assertOk()
        ->assertJsonCount(1, 'cards');
});

it('respects the new cards per day limit', function (): void {
    $deck = Deck::factory()->for($this->user)->create(['new_cards_per_day' => 2]);
    Card::factory()->for($deck)->count(10)->create();

    $this->actingAs($this->user)
        ->getJson(route('study.cards', $deck))
        ->assertOk()
        ->assertJsonCount(2, 'cards');
});

it('shows every due review, whatever the new card limit', function (): void {
    $deck = Deck::factory()->for($this->user)->create(['new_cards_per_day' => 2]);
    Card::factory()->for($deck)->due()->count(5)->create();
    Card::factory()->for($deck)->count(10)->create();

    // 5 reviews + 2 new. One limit over the whole queue used to return 2 cards.
    $this->actingAs($this->user)
        ->getJson(route('study.cards', $deck))
        ->assertOk()
        ->assertJsonCount(7, 'cards');
});

it('counts new cards already started today against the daily limit', function (): void {
    $deck = Deck::factory()->for($this->user)->create(['new_cards_per_day' => 3]);
    $cards = Card::factory()->for($deck)->count(10)->create();

    $this->actingAs($this->user)->postJson(route('study.batch-update'), ['updates' => [
        ['card_id' => $cards[0]->id, 'rating' => 3],
        ['card_id' => $cards[1]->id, 'rating' => 3],
    ]])->assertOk();

    // Those two are now in learning and not due for minutes; one new card is left
    // for today. A reload used to hand out three more.
    $this->actingAs($this->user)
        ->getJson(route('study.cards', $deck))
        ->assertOk()
        ->assertJsonCount(1, 'cards');
});

it('tells the study screen how soon each rated card is due again', function (): void {
    $card = Card::factory()->for(Deck::factory()->for($this->user))->create();

    $this->actingAs($this->user)->postJson(route('study.batch-update'), ['updates' => [
        ['card_id' => $card->id, 'rating' => 1, 'client_uuid' => (string) Str::uuid()],
    ]])->assertOk()
        ->assertJsonPath('updated_cards.0.state', 'learning')
        ->assertJsonPath('updated_cards.0.scheduled_seconds', 60);
});

it('applies a retried save only once', function (): void {
    $card = Card::factory()->for(Deck::factory()->for($this->user))->create();
    $update = ['card_id' => $card->id, 'rating' => 3, 'client_uuid' => (string) Str::uuid()];

    $this->actingAs($this->user)->postJson(route('study.batch-update'), ['updates' => [$update]])->assertOk();
    $this->actingAs($this->user)->postJson(route('study.batch-update'), ['updates' => [$update]])->assertOk();

    expect(ReviewLog::count())->toBe(1)
        ->and($card->fresh()->reps)->toBe(1);
});

it('counts new static cards started today against the daily limit', function (): void {
    $deck = StaticDeck::factory()->create();
    $cards = StaticCard::factory()->for($deck, 'staticDeck')->count(10)->create();

    $this->actingAs($this->user)->post(route('static-decks.cards-per-day', $deck), ['cards_per_day' => 4]);

    $this->actingAs($this->user)->postJson(route('static-study.batch-update'), ['updates' => [
        ['card_id' => $cards[0]->id, 'rating' => 3],
        ['card_id' => $cards[1]->id, 'rating' => 3],
        ['card_id' => $cards[2]->id, 'rating' => 3],
    ]])->assertOk()->assertJsonCount(3, 'updated_cards');

    $this->actingAs($this->user)
        ->getJson(route('static-study.cards', $deck))
        ->assertOk()
        ->assertJsonCount(1, 'cards');
});

it('counts distinct new cards in static progress, not ratings', function (): void {
    $deck = StaticDeck::factory()->create();
    $card = StaticCard::factory()->for($deck, 'staticDeck')->create();
    StaticCard::factory()->for($deck, 'staticDeck')->count(4)->create();

    // The same card three times: Again, Again, Good.
    foreach ([1, 1, 3] as $rating) {
        $this->actingAs($this->user)->postJson(route('static-study.batch-update'), ['updates' => [
            ['card_id' => $card->id, 'rating' => $rating],
        ]])->assertOk();
    }

    expect(App\Models\UserStaticDeckProgress::where('user_id', $this->user->id)->sole()->cards_studied)->toBe(1);
});

// -------------------------------------------------------------------------
// Review endpoint
// -------------------------------------------------------------------------

it('records a review over http and schedules the card', function (): void {
    $deck = Deck::factory()->for($this->user)->create();
    $card = Card::factory()->for($deck)->create();

    $response = $this->actingAs($this->user)->postJson(route('study.update-card', $card), [
        'rating' => 3,
        'review_duration_ms' => 2100,
    ]);

    $response->assertOk()->assertJson(['success' => true]);

    $card->refresh();

    expect($card->state)->toBe(CardState::Learning)
        ->and(round((float) $card->stability, 4))->toBe(2.3065)
        ->and(ReviewLog::sole()->review_duration_ms)->toBe(2100);
});

it('rejects a rating outside 1 to 4', function (int $rating): void {
    $deck = Deck::factory()->for($this->user)->create();
    $card = Card::factory()->for($deck)->create();

    $this->actingAs($this->user)
        ->postJson(route('study.update-card', $card), ['rating' => $rating])
        ->assertStatus(422);
})->with([0, 5, -1]);

// -------------------------------------------------------------------------
// Authorization: the IDOR that used to be open here
// -------------------------------------------------------------------------

it('does not let another user read a private deck queue', function (): void {
    $deck = Deck::factory()->for(User::factory())->private()->create();
    Card::factory()->for($deck)->create();

    $this->actingAs($this->user)
        ->getJson(route('study.cards', $deck))
        ->assertForbidden();
});

it('does let anyone read a public deck queue', function (): void {
    // DeckPolicy::view() passes for public decks by design, so reading is allowed.
    // Writing is not: the review endpoints authorize 'update', which is owner-only.
    $deck = Deck::factory()->for(User::factory())->public()->create();
    Card::factory()->for($deck)->create();

    $this->actingAs($this->user)
        ->getJson(route('study.cards', $deck))
        ->assertOk();
});

it('does not let another user review a card in a public deck', function (): void {
    $deck = Deck::factory()->for(User::factory())->public()->create();
    $card = Card::factory()->for($deck)->create();

    $this->actingAs($this->user)
        ->postJson(route('study.update-card', $card), ['rating' => 3])
        ->assertForbidden();

    expect($card->fresh()->reps)->toBe(0);
});

it('does not let another user review a card', function (): void {
    $deck = Deck::factory()->for(User::factory())->create();
    $card = Card::factory()->for($deck)->create();

    $this->actingAs($this->user)
        ->postJson(route('study.update-card', $card), ['rating' => 3])
        ->assertForbidden();

    expect(ReviewLog::count())->toBe(0)
        ->and($card->fresh()->reps)->toBe(0);
});

it('rejects a batch containing a card the caller does not own', function (): void {
    $mine = Card::factory()->for(Deck::factory()->for($this->user))->create();
    $theirs = Card::factory()->for(Deck::factory()->for(User::factory()))->create();

    $this->actingAs($this->user)
        ->postJson(route('study.batch-update'), [
            'updates' => [
                ['card_id' => $mine->id, 'rating' => 3],
                ['card_id' => $theirs->id, 'rating' => 3],
            ],
        ])
        ->assertForbidden();

    // Authorization runs before any write, so neither card moved.
    expect(ReviewLog::count())->toBe(0)
        ->and($mine->fresh()->reps)->toBe(0)
        ->and($theirs->fresh()->reps)->toBe(0);
});

it('returns 403 rather than 500 when authorization fails', function (): void {
    // AuthorizationException extends Exception, so authorizing inside the
    // controller's try/catch would surface as a generic 500.
    $deck = Deck::factory()->for(User::factory())->create();
    $card = Card::factory()->for($deck)->create();

    $this->actingAs($this->user)
        ->postJson(route('study.update-card', $card), ['rating' => 3])
        ->assertStatus(403);
});

it('applies a whole batch the caller does own', function (): void {
    $deck = Deck::factory()->for($this->user)->create();
    $cards = Card::factory()->for($deck)->count(3)->create();

    $this->actingAs($this->user)
        ->postJson(route('study.batch-update'), [
            'updates' => $cards->map(fn (Card $c): array => ['card_id' => $c->id, 'rating' => 3])->all(),
        ])
        ->assertOk()
        ->assertJson(['success' => true]);

    expect(ReviewLog::count())->toBe(3)
        ->and($cards->each->refresh()->every(fn (Card $c): bool => $c->reps === 1))->toBeTrue();
});

// -------------------------------------------------------------------------
// Static decks: shared content, per-user schedule
// -------------------------------------------------------------------------

it('keeps static deck queues independent per user', function (): void {
    $deck = StaticDeck::factory()->create();
    $cards = StaticCard::factory()->for($deck, 'staticDeck')->count(3)->create();
    $other = User::factory()->create();

    // This user answers everything, so nothing is left due for them today.
    foreach ($cards as $card) {
        $this->actingAs($this->user)
            ->postJson(route('static-study.update-card', $card), ['rating' => 4])
            ->assertOk();
    }

    // The other user has not started, so all three are still waiting.
    $this->actingAs($other)
        ->getJson(route('static-study.cards', $deck))
        ->assertOk()
        ->assertJsonCount(3, 'cards');

    expect(UserStaticCardState::where('user_id', $this->user->id)->count())->toBe(3)
        ->and(UserStaticCardState::where('user_id', $other->id)->count())->toBe(0);
});

it('caps the static json queue at the cards per day setting', function (): void {
    $deck = StaticDeck::factory()->create();
    StaticCard::factory()->for($deck, 'staticDeck')->count(10)->create();

    $this->actingAs($this->user)
        ->post(route('static-decks.cards-per-day', $deck), ['cards_per_day' => 4])
        ->assertRedirect();

    // This endpoint used to apply no limit at all, so the daily cap was enforced
    // on the rendered page and silently bypassed through JSON.
    $this->actingAs($this->user)
        ->getJson(route('static-study.cards', $deck))
        ->assertOk()
        ->assertJsonCount(4, 'cards');
});

it('resets only the acting user progress on a static deck', function (): void {
    $deck = StaticDeck::factory()->create();
    $card = StaticCard::factory()->for($deck, 'staticDeck')->create();
    $other = User::factory()->create();

    foreach ([$this->user, $other] as $user) {
        $this->actingAs($user)->postJson(route('static-study.update-card', $card), ['rating' => 3])->assertOk();
    }

    $this->actingAs($this->user)->post(route('static-decks.reset', $deck))->assertRedirect();

    expect(UserStaticCardState::where('user_id', $this->user->id)->sole()->reps)->toBe(0)
        ->and(UserStaticCardState::where('user_id', $other->id)->sole()->reps)->toBe(1);
});

// -------------------------------------------------------------------------
// Offline replay through the HTTP layer
// -------------------------------------------------------------------------

it('deduplicates a replayed offline review over http', function (): void {
    $deck = Deck::factory()->for($this->user)->create();
    $card = Card::factory()->for($deck)->create();
    $uuid = (string) Str::uuid();

    foreach (range(1, 3) as $attempt) {
        $this->actingAs($this->user)
            ->postJson(route('study.update-card', $card), ['rating' => 3, 'client_uuid' => $uuid])
            ->assertOk();
    }

    expect(ReviewLog::count())->toBe(1)
        ->and($card->fresh()->reps)->toBe(1);
});
