<?php

declare(strict_types=1);

use App\Constants\DeckLimits;
use App\Models\Card;
use App\Models\Deck;
use App\Models\User;
use App\Services\WordListService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Http::preventStrayRequests();

    config([
        'services.word_lookup.definitions' => ['wiktionary'],
        'services.word_lookup.translators' => ['mymemory'],
    ]);

    $this->user = User::factory()->create();
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function word(array $overrides = []): array
{
    return [
        'front' => 'ahead',
        'back' => 'پیش رو',
        'description' => 'adverb: In or for the future. Example: There may be tough times ahead.',
        'pronunciation' => '/əˈhɛd/',
        ...$overrides,
    ];
}

it('keeps guests out', function (): void {
    $this->getJson(route('words.lookup', ['q' => 'ahead']))->assertRedirect(route('login.page'));
    $this->postJson(route('word-lists.add-word'), word())->assertRedirect(route('login.page'));

    expect(Card::count())->toBe(0);
});

it('looks a word up and answers with the card it suggests', function (): void {
    Http::fake([
        'en.wiktionary.org/*' => Http::response(['en' => [[
            'partOfSpeech' => 'Adverb',
            'definitions' => [['definition' => 'In or for the future.', 'examples' => ['Tough times <b>ahead</b>.']]],
        ]]]),
        'api.mymemory.translated.net/*' => Http::response([
            'responseStatus' => 200,
            'responseData' => ['translatedText' => 'پیش رو.'],
            'matches' => [],
        ]),
    ]);

    $this->actingAs($this->user)
        ->getJson(route('words.lookup', ['q' => 'ahead']))
        ->assertOk()
        ->assertJsonPath('language', 'en')
        ->assertJsonPath('word', 'ahead')
        ->assertJsonPath('translations', ['پیش رو'])
        ->assertJsonPath('senses.0.part_of_speech', 'adverb')
        ->assertJsonPath('card.front', 'ahead')
        ->assertJsonPath('card.back', 'پیش رو')
        ->assertJsonPath('card.description', 'adverb: In or for the future. Example: Tough times ahead.');
});

it('still answers when no service knows the word, so it can be typed in by hand', function (): void {
    Http::fake([
        'en.wiktionary.org/*' => Http::response([], 404),
        'api.mymemory.translated.net/*' => Http::failedConnection(),
    ]);

    $this->actingAs($this->user)
        ->getJson(route('words.lookup', ['q' => 'zzqxv']))
        ->assertOk()
        ->assertJsonPath('senses', [])
        ->assertJsonPath('translations', [])
        ->assertJsonPath('card.front', 'zzqxv')
        ->assertJsonPath('card.back', '');
});

it('validates the word to look up', function (): void {
    $this->actingAs($this->user)->getJson(route('words.lookup'))->assertUnprocessable()->assertJsonValidationErrors('q');
    $this->actingAs($this->user)->getJson(route('words.lookup', ['q' => str_repeat('a', 101)]))->assertJsonValidationErrors('q');
});

it('saves the first word into a default list it creates', function (): void {
    $this->actingAs($this->user)
        ->postJson(route('word-lists.add-word'), word())
        ->assertCreated()
        ->assertJsonPath('created', true)
        ->assertJsonPath('list.name', WordListService::DEFAULT_LIST_NAME)
        ->assertJsonPath('list.is_default_list', true)
        ->assertJsonPath('list.cards_count', 1)
        ->assertJsonPath('message', 'Added "ahead" to My Words.');

    $list = $this->user->decks()->sole();
    $card = $list->cards()->sole();

    expect($list->is_default_list)->toBeTrue()
        ->and($list->new_cards_per_day)->toBe(10)
        ->and($card->front)->toBe('ahead')
        ->and($card->back)->toBe('پیش رو')
        ->and($card->description)->toBe('adverb: In or for the future. Example: There may be tough times ahead.')
        ->and($card->audio)->toBe(['pronunciation' => '/əˈhɛd/'])
        // A saved word is an ordinary new card, ready to study.
        ->and($card->state->value)->toBe('new');
});

it('does not save the same word twice in one list', function (): void {
    $this->actingAs($this->user)->postJson(route('word-lists.add-word'), word())->assertCreated();

    $this->actingAs($this->user)
        ->postJson(route('word-lists.add-word'), word(['front' => 'Ahead', 'back' => 'جلو']))
        ->assertOk()
        ->assertJsonPath('created', false)
        ->assertJsonPath('card.back', 'پیش رو')
        ->assertJsonPath('message', '"ahead" is already in My Words.');

    expect(Card::count())->toBe(1);
});

it('saves into the list picked last, and keeps doing so', function (): void {
    $verbs = Deck::factory()->for($this->user)->create(['name' => 'Verbs']);

    $this->actingAs($this->user)
        ->putJson(route('word-lists.select'), ['list_id' => $verbs->id])
        ->assertOk()
        ->assertJsonPath('list_id', $verbs->id);

    // Next request, no list named: the remembered one.
    $this->actingAs($this->user->fresh())
        ->postJson(route('word-lists.add-word'), word(['front' => 'win', 'back' => 'بردن']))
        ->assertCreated()
        ->assertJsonPath('list.id', $verbs->id);

    expect($verbs->cards()->pluck('front')->all())->toBe(['win'])
        ->and($this->user->decks()->where('is_default_list', true)->exists())->toBeFalse();
});

it('remembers the list a word was saved into', function (): void {
    $verbs = Deck::factory()->for($this->user)->create(['name' => 'Verbs']);

    $this->actingAs($this->user)->postJson(route('word-lists.add-word'), word(['list_id' => $verbs->id]))->assertCreated();
    $this->actingAs($this->user->fresh())->postJson(route('word-lists.add-word'), word(['front' => 'win']))->assertCreated();

    expect($verbs->cards()->count())->toBe(2)
        ->and(app(WordListService::class)->selectedListIdFor($this->user->fresh()))->toBe($verbs->id);
});

it('goes back to the default list when the picked one is deleted', function (): void {
    $verbs = Deck::factory()->for($this->user)->create(['name' => 'Verbs']);
    $this->actingAs($this->user)->putJson(route('word-lists.select'), ['list_id' => $verbs->id])->assertOk();

    $verbs->delete();

    $this->actingAs($this->user->fresh())
        ->postJson(route('word-lists.add-word'), word())
        ->assertCreated()
        ->assertJsonPath('list.is_default_list', true);
});

it('saves into the default list when the word names it, whatever was picked before', function (): void {
    // The picker remembers a pick in its own request. A word saved right after
    // picking the default list can arrive first; naming the list makes the
    // order irrelevant. Found in the browser, where "book" landed in Verbs.
    $verbs = Deck::factory()->for($this->user)->create(['name' => 'Verbs']);
    $this->actingAs($this->user)->putJson(route('word-lists.select'), ['list_id' => $verbs->id])->assertOk();

    $this->actingAs($this->user->fresh())
        ->postJson(route('word-lists.add-word'), word(['list_id' => 'default']))
        ->assertCreated()
        ->assertJsonPath('list.is_default_list', true);

    expect($verbs->cards()->count())->toBe(0)
        ->and(app(WordListService::class)->selectedListIdFor($this->user->fresh()))
        ->toBe($this->user->decks()->where('is_default_list', true)->value('id'));
});

it('refuses a list that is neither an id nor "default"', function (mixed $listId): void {
    $this->actingAs($this->user)
        ->postJson(route('word-lists.add-word'), word(['list_id' => $listId]))
        ->assertJsonValidationErrors('list_id');
})->with(['word' => ['mine'], 'zero' => [0], 'fraction' => [1.5]]);

it('goes back to the default list when asked to', function (): void {
    $verbs = Deck::factory()->for($this->user)->create();
    $this->actingAs($this->user)->putJson(route('word-lists.select'), ['list_id' => $verbs->id])->assertOk();

    $this->actingAs($this->user->fresh())->putJson(route('word-lists.select'), ['list_id' => null])->assertOk();

    $this->actingAs($this->user->fresh())
        ->postJson(route('word-lists.add-word'), word())
        ->assertJsonPath('list.is_default_list', true);
});

it('never saves into, or picks, another user\'s list -- not even a public one', function (): void {
    $theirs = Deck::factory()->public()->create();

    $this->actingAs($this->user)
        ->postJson(route('word-lists.add-word'), word(['list_id' => $theirs->id]))
        ->assertForbidden();

    $this->actingAs($this->user)
        ->putJson(route('word-lists.select'), ['list_id' => $theirs->id])
        ->assertForbidden();

    expect($theirs->cards()->count())->toBe(0)
        ->and($this->user->fresh()->preference('word_list_id'))->toBeNull();
});

it('creates a new list from the picker and picks it', function (): void {
    $this->actingAs($this->user)
        ->postJson(route('word-lists.store'), ['name' => 'Phrasal verbs'])
        ->assertCreated()
        ->assertJsonPath('list.name', 'Phrasal verbs')
        ->assertJsonPath('list.is_default_list', false)
        ->assertJsonPath('list.cards_count', 0);

    $list = $this->user->decks()->sole();

    $this->actingAs($this->user->fresh())
        ->postJson(route('word-lists.add-word'), word(['front' => 'look forward to', 'back' => 'مشتاق بودن']))
        ->assertJsonPath('list.id', $list->id);
});

it('refuses a second list with the same name', function (): void {
    Deck::factory()->for($this->user)->create(['name' => 'Phrasal verbs']);

    $this->actingAs($this->user)
        ->postJson(route('word-lists.store'), ['name' => 'phrasal VERBS'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');

    // Another user's list of that name is no obstacle.
    Deck::factory()->create(['name' => 'Idioms']);

    $this->actingAs($this->user)->postJson(route('word-lists.store'), ['name' => 'Idioms'])->assertCreated();
});

it('holds new lists to the deck limit', function (): void {
    Deck::factory()->count(DeckLimits::USER_MAX_DECKS)->for($this->user)->create();

    $this->actingAs($this->user)
        ->postJson(route('word-lists.store'), ['name' => 'One too many'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

it('holds words to the card limit of their list', function (): void {
    $list = Deck::factory()->for($this->user)->create();

    // One insert, not 2,000 factory saves.
    DB::table('cards')->insert(array_map(fn (int $i): array => [
        'deck_id' => $list->id,
        'front' => "word {$i}",
        'back' => 'معنی',
        'created_at' => now(),
        'updated_at' => now(),
    ], range(1, DeckLimits::USER_DECK_MAX_CARDS)));

    $this->actingAs($this->user)
        ->postJson(route('word-lists.add-word'), word(['list_id' => $list->id]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('list_id');
});

it('validates the card', function (): void {
    $this->actingAs($this->user)
        ->postJson(route('word-lists.add-word'), word(['front' => '', 'back' => str_repeat('x', 1001)]))
        ->assertJsonValidationErrors(['front', 'back']);

    expect(Deck::count())->toBe(0);
});

it('gives each user exactly one default list', function (): void {
    $other = User::factory()->create();
    $service = app(WordListService::class);

    $mine = $service->defaultListFor($this->user);
    $again = $service->defaultListFor($this->user);
    $theirs = $service->defaultListFor($other);

    expect($again->id)->toBe($mine->id)
        ->and($theirs->id)->not->toBe($mine->id)
        ->and(Deck::where('is_default_list', true)->count())->toBe(2);

    // The database holds the line too, for two requests racing past the check.
    expect(fn () => Deck::factory()->for($this->user)->create(['is_default_list' => true]))
        ->toThrow(UniqueConstraintViolationException::class);
});

it('shows each user only their own lists, the default one first', function (): void {
    $other = User::factory()->create();
    Deck::factory()->for($other)->create(['name' => 'Their secret list']);
    Deck::factory()->for($this->user)->create(['name' => 'Verbs']);
    $default = app(WordListService::class)->defaultListFor($this->user);

    $response = $this->actingAs($this->user)->get(route('dashboard'))->assertOk();

    $response->assertViewHas('lists', fn (array $lists): bool => array_column($lists, 'name') === [WordListService::DEFAULT_LIST_NAME, 'Verbs'])
        ->assertViewHas('selectedListId', $default->id)
        ->assertSee('Look up a word')
        ->assertSee('Default word list')
        ->assertDontSee('Their secret list');
});

it('creates nothing just by showing the dashboard', function (): void {
    $this->actingAs($this->user)->get(route('dashboard'))
        ->assertOk()
        ->assertViewHas('selectedListId', null)
        ->assertViewHas('lists', []);

    expect(Deck::count())->toBe(0);
});

it('opens the lookup page on a word, the way another app links to it', function (): void {
    $verbs = Deck::factory()->for($this->user)->create(['name' => 'Verbs']);
    $this->user->setPreference('word_list_id', $verbs->id);

    // No outside call here: the page looks the word up from the browser.
    $this->actingAs($this->user)
        ->get('/lookup?q=' . rawurlencode('  پیش رو '))
        ->assertOk()
        ->assertViewHas('query', 'پیش رو')
        ->assertViewHas('selectedListId', $verbs->id)
        ->assertViewHas('lists', fn (array $lists): bool => array_column($lists, 'name') === ['Verbs'])
        ->assertSee('Look up a word');
});

it('opens the lookup page empty when q is missing or not a word', function (string $uri): void {
    $this->actingAs($this->user)->get($uri)->assertOk()->assertViewHas('query', '');
})->with(['no q' => ['/lookup'], 'q as an array' => ['/lookup?q[]=ahead']]);

it('keeps the word through signing in', function (): void {
    $intended = url('/lookup?q=ahead');

    $this->get('/lookup?q=ahead')
        ->assertRedirect(route('login.page'))
        ->assertSessionHas('url.intended', $intended);

    $googleUser = (new GoogleUser)->map([
        'email' => $this->user->email,
        'name' => $this->user->name,
        'avatar' => $this->user->avatar,
    ]);
    Socialite::shouldReceive('driver->user')->andReturn($googleUser);

    $this->get('/auth/google/callback')->assertRedirect($intended);
    $this->assertAuthenticatedAs($this->user);
});

it('still sends a returning user to the dashboard by default', function (): void {
    $googleUser = (new GoogleUser)->map(['email' => $this->user->email, 'name' => $this->user->name, 'avatar' => null]);
    Socialite::shouldReceive('driver->user')->andReturn($googleUser);

    $this->get('/auth/google/callback')->assertRedirect(route('dashboard'));
});

it('takes the direction in the page URL, the way Google Translate does', function (string $query, string $direction): void {
    $this->actingAs($this->user)
        ->get('/lookup?' . $query)
        ->assertOk()
        ->assertViewHas('query', 'later')
        ->assertViewHas('direction', $direction);
})->with([
    'sl=en&tl=fa' => ['q=later&sl=en&tl=fa', 'en-fa'],
    'sl=fa&tl=en' => ['q=later&sl=fa&tl=en', 'fa-en'],
    'sl=en&tl=en' => ['q=later&sl=en&tl=en', 'en-en'],
    'tl alone' => ['q=later&tl=en', 'fa-en'],
    'detect' => ['q=later', 'auto'],
    'sl=auto' => ['q=later&sl=auto&tl=fa', 'auto'],
    'unknown language' => ['q=later&sl=de', 'auto'],
    // Google Translate's own parameter names, op= and all.
    'a Google Translate URL with the host changed' => ['sl=en&tl=fa&text=later&op=translate', 'en-fa'],
]);

it('looks up in the direction asked for', function (): void {
    Http::fake([
        'api.mymemory.translated.net/*' => Http::response([
            'responseStatus' => 200,
            'responseData' => ['translatedText' => 'later'],
            'matches' => [],
        ]),
    ]);

    $this->actingAs($this->user)
        ->getJson(route('words.lookup', ['q' => 'later', 'sl' => 'fa', 'tl' => 'en']))
        ->assertOk()
        ->assertJsonPath('language', 'fa')
        ->assertJsonPath('card.front', 'later')
        ->assertJsonPath('card.back', 'later');

    Http::assertSent(fn ($request): bool => $request['langpair'] === 'fa|en');
    Http::assertNotSent(fn ($request): bool => str_contains($request->url(), 'wiktionary'));
});

it('explains a word in English when asked for English to English', function (): void {
    Http::fake([
        'en.wiktionary.org/*' => Http::response(['en' => [[
            'partOfSpeech' => 'Interjection',
            'definitions' => [['definition' => 'A greeting said when meeting someone.', 'examples' => ['<b>Hello</b>, everyone.']]],
        ]]]),
    ]);

    $this->actingAs($this->user)
        ->getJson(route('words.lookup', ['q' => 'hello', 'sl' => 'en', 'tl' => 'en']))
        ->assertOk()
        ->assertJsonPath('direction', 'en-en')
        ->assertJsonPath('target', 'en')
        ->assertJsonPath('translations', [])
        ->assertJsonPath('card.front', 'hello')
        ->assertJsonPath('card.back', 'A greeting said when meeting someone.')
        ->assertJsonPath('card.description', 'Example: Hello, everyone.')
        ->assertJsonPath('senses.0.back', 'A greeting said when meeting someone.');

    Http::assertNotSent(fn ($request): bool => str_contains($request->url(), 'mymemory'));
});

it('refuses a direction it does not know', function (): void {
    $this->actingAs($this->user)
        ->getJson(route('words.lookup', ['q' => 'later', 'sl' => 'de']))
        ->assertJsonValidationErrors('sl');
});

it('links every word in a list to its MemFlash lookup', function (): void {
    $list = Deck::factory()->for($this->user)->create();
    Card::factory()->for($list)->create(['front' => 'look forward to']);

    $this->actingAs($this->user)
        ->get(route('decks.show', $list))
        ->assertOk()
        ->assertSee('href="' . url('/lookup?q=look%20forward%20to') . '"', false)
        ->assertDontSee('translate.google.com');
});
