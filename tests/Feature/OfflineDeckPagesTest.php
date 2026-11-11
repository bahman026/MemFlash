<?php

declare(strict_types=1);

use App\Models\Card;
use App\Models\Deck;
use App\Models\StaticDeck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

it('asks the browser to keep an owned deck for offline use when it is opened', function (): void {
    $deck = Deck::factory()->for($this->user)->create();
    Card::factory()->for($deck)->count(2)->create();

    $this->actingAs($this->user)->get(route('decks.show', $deck))
        ->assertOk()
        ->assertSee('<meta name="memflash-offline-deck" content="card:' . $deck->id . '">', false)
        ->assertSee('<meta name="memflash-offline-assets"', false)
        ->assertSee('data-offline-notice', false);
});

it('does not try to keep someone else\'s public deck offline', function (): void {
    // The sync bootstrap refuses it, so asking would only produce a 404.
    $deck = Deck::factory()->for(User::factory())->public()->create();

    $this->actingAs($this->user)->get(route('decks.show', $deck))
        ->assertOk()
        ->assertDontSee('memflash-offline-deck', false);
});

it('asks the browser to keep a lesson for offline use from its page and preview', function (string $routeName): void {
    $lesson = StaticDeck::factory()->create();

    $this->actingAs($this->user)->get(route($routeName, $lesson))
        ->assertOk()
        ->assertSee('<meta name="memflash-offline-deck" content="static_card:' . $lesson->id . '">', false);
})->with(['static-decks.show', 'static-decks.preview']);

it('renders a lesson\'s study page with nothing due, so it can be kept for offline use', function (): void {
    // It used to redirect back. The service worker never keeps a redirect, so a
    // lesson with nothing due at download time could not be studied offline later.
    $lesson = StaticDeck::factory()->create();

    $this->actingAs($this->user)->get(route('static-decks.study', $lesson))
        ->assertOk()
        ->assertSee('staticDeckId: ' . $lesson->id, false);
});
