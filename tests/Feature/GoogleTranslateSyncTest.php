<?php

declare(strict_types=1);

use App\Constants\DeckLimits;
use App\Enums\UserStatusEnum;
use App\Models\Card;
use App\Models\Deck;
use App\Models\GoogleTranslateSync;
use App\Models\User;
use App\Services\GoogleTranslate\SavedWordsSync;
use App\Services\GoogleTranslate\SheetLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

const SHEET = 'https://docs.google.com/spreadsheets/d/1AbC-xyz_123/edit#gid=42';
const SHEET_CSV = 'https://docs.google.com/spreadsheets/d/1AbC-xyz_123/export?format=csv&gid=42';

beforeEach(function (): void {
    Http::preventStrayRequests();

    // The sheet as Google serves it right now; savedWords() changes it. One
    // fake that reads it, because a second Http::fake() for the same URL
    // would not replace the first.
    $this->sheet = fn () => Http::response('', 404);
    Http::fake([SHEET_CSV => fn () => ($this->sheet)()]);

    $this->user = User::factory()->create();
});

/**
 * Google Translate's export: source language, target language, text, translation.
 */
function savedWords(string ...$rows): void
{
    test()->sheet = fn () => Http::response(implode("\n", $rows), 200, ['Content-Type' => 'text/csv']);
}

function linkSheet(User $user, ?Deck $list = null): GoogleTranslateSync
{
    return $user->googleTranslateSync()->create(['sheet_url' => SHEET, 'deck_id' => $list?->id]);
}

it('reads the CSV export of a sheet link, a tab and a published sheet', function (): void {
    expect(SheetLink::parse(SHEET)?->csvUrl)->toBe(SHEET_CSV)
        ->and(SheetLink::parse('https://docs.google.com/spreadsheets/d/1AbC/edit?usp=sharing')?->csvUrl)
        ->toBe('https://docs.google.com/spreadsheets/d/1AbC/export?format=csv')
        ->and(SheetLink::parse('https://docs.google.com/spreadsheets/d/e/2PACX-1v/pubhtml?gid=7&single=true')?->csvUrl)
        ->toBe('https://docs.google.com/spreadsheets/d/e/2PACX-1v/pub?output=csv&gid=7');
});

it('refuses links that are not Google Sheets', function (string $url): void {
    expect(SheetLink::parse($url))->toBeNull();
})->with([
    'another host' => 'https://evil.example/spreadsheets/d/1AbC/edit',
    'a lookalike host' => 'https://docs.google.com.evil.example/spreadsheets/d/1AbC',
    'a document' => 'https://docs.google.com/document/d/1AbC/edit',
    'not a URL' => 'my words',
    'another scheme' => 'file://docs.google.com/spreadsheets/d/1AbC',
]);

it('turns each row into an English front and a Persian back, whichever way it was saved', function (): void {
    $sync = app(SavedWordsSync::class);

    expect($sync->pair(['English', 'Persian', 'win', 'برنده شدن']))->toBe(['win', 'برنده شدن'])
        ->and($sync->pair(['Persian', 'English', 'پيروز', 'victorious.']))->toBe(['victorious', 'پیروز'])
        ->and($sync->pair(['انگلیسی', 'فارسی', 'ahead', 'پیش رو']))->toBe(['ahead', 'پیش رو'])
        ->and($sync->pair(['ahead', 'پیش رو']))->toBe(['ahead', 'پیش رو'])
        ->and($sync->pair(['English', 'German', 'win', 'gewinnen']))->toBeNull()
        ->and($sync->pair(['Japanese', 'Persian', '勝つ', 'برنده شدن']))->toBeNull()
        ->and($sync->pair(['English', 'Persian', 'win', '']))->toBeNull()
        ->and($sync->pair(['win']))->toBeNull();
});

it('links a sheet from the dashboard and brings its words into the default list', function (): void {
    savedWords(
        '"English","Persian","win","برنده شدن"',
        '"English","Persian","look forward to","مشتاقانه منتظر بودن"',
        '"English","German","win","gewinnen"',
    );

    $this->actingAs($this->user)
        ->from(route('dashboard'))
        ->put(route('google-translate.connect'), ['sheet_url' => SHEET, 'list_id' => 'default'])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('success', 'Added 2 new words from Google Translate to My Words. 1 row was not English ↔ Persian and was skipped.');

    $list = $this->user->decks()->sole();
    expect($list->is_default_list)->toBeTrue()
        ->and($list->cards()->orderBy('id')->pluck('back', 'front')->all())
        ->toBe(['win' => 'برنده شدن', 'look forward to' => 'مشتاقانه منتظر بودن']);

    $sync = $this->user->googleTranslateSync;
    expect($sync->deck_id)->toBeNull()
        ->and($sync->last_added)->toBe(2)
        ->and($sync->last_synced_at)->not->toBeNull()
        ->and($sync->imported_words)->toBe(['win', 'look forward to']);
});

it('only adds what is new, and never brings back a word deleted here', function (): void {
    $list = Deck::factory()->for($this->user)->create(['name' => 'Saved']);
    $list->cards()->create(['front' => 'Ahead', 'back' => 'جلو']);
    $sync = linkSheet($this->user, $list);

    savedWords('English,Persian,ahead,پیش رو', 'English,Persian,win,برنده شدن');
    expect(app(SavedWordsSync::class)->sync($sync)->added)->toBe(1);

    $list->cards()->where('front', 'win')->delete();

    savedWords('English,Persian,ahead,پیش رو', 'English,Persian,win,برنده شدن', 'English,Persian,kick off,شروع کردن');
    $result = app(SavedWordsSync::class)->sync($sync->fresh());

    expect($result->added)->toBe(1)
        ->and($result->unchanged)->toBe(2)
        ->and($list->cards()->pluck('front')->sort()->values()->all())->toBe(['Ahead', 'kick off'])
        ->and($sync->fresh()->imported_words)->toContain('kick off');
});

it('stops at the list size and says how many were left out', function (): void {
    $list = Deck::factory()->for($this->user)->create(['name' => 'Saved']);
    Card::factory()->for($list)->count(DeckLimits::USER_DECK_MAX_CARDS - 1)->create();
    $sync = linkSheet($this->user, $list);

    savedWords('English,Persian,win,برنده شدن', 'English,Persian,ahead,پیش رو');
    $result = app(SavedWordsSync::class)->sync($sync);

    expect($result->added)->toBe(1)
        ->and($result->leftOut)->toBe(1)
        ->and($result->message())->toContain('Saved is full, so 1 word was left out.')
        // Left out, not imported: it comes in once there is room.
        ->and($sync->fresh()->imported_words)->toBe(['win']);
});

it('says so when the sheet is not shared, and keeps the link to retry', function (): void {
    // A private sheet redirects to Google's sign-in page.
    $this->sheet = fn () => Http::response('<html>Sign in</html>', 200, ['Content-Type' => 'text/html; charset=utf-8']);

    $this->actingAs($this->user)
        ->from(route('dashboard'))
        ->put(route('google-translate.connect'), ['sheet_url' => SHEET])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('error', fn (string $message): bool => str_contains($message, 'Anyone with the link'));

    expect($this->user->googleTranslateSync->last_error)->toContain('Anyone with the link')
        ->and(Card::count())->toBe(0);

    savedWords('English,Persian,win,برنده شدن');

    $this->actingAs($this->user)->post(route('google-translate.sync'))->assertSessionHas('success');

    expect($this->user->googleTranslateSync()->sole()->last_error)->toBeNull()
        ->and(Card::count())->toBe(1);
});

it('rejects a link that is not a Google Sheet without fetching anything', function (): void {
    $this->actingAs($this->user)
        ->put(route('google-translate.connect'), ['sheet_url' => 'https://example.com/words.csv'])
        ->assertSessionHasErrors('sheet_url');

    expect(GoogleTranslateSync::count())->toBe(0);
});

it('will not sync into someone else\'s deck, public or not', function (): void {
    $theirs = Deck::factory()->public()->create();

    $this->actingAs($this->user)
        ->put(route('google-translate.connect'), ['sheet_url' => SHEET, 'list_id' => $theirs->id])
        ->assertForbidden();

    expect(GoogleTranslateSync::count())->toBe(0);
});

it('starts the imported words over when the list changes', function (): void {
    $sync = linkSheet($this->user);
    $sync->update(['imported_words' => ['win']]);
    $other = Deck::factory()->for($this->user)->create();

    savedWords('English,Persian,win,برنده شدن');

    $this->actingAs($this->user)
        ->put(route('google-translate.connect'), ['sheet_url' => SHEET, 'list_id' => $other->id])
        ->assertSessionHas('success');

    expect($other->cards()->pluck('front')->all())->toBe(['win']);
});

it('stops syncing and keeps the words', function (): void {
    $sync = linkSheet($this->user);
    savedWords('English,Persian,win,برنده شدن');
    app(SavedWordsSync::class)->sync($sync);

    $this->actingAs($this->user)->delete(route('google-translate.disconnect'))->assertSessionHas('success');

    expect(GoogleTranslateSync::count())->toBe(0)
        ->and(Card::count())->toBe(1);
});

it('syncs everyone from the daily command, skipping blocked users', function (): void {
    linkSheet($this->user);
    $blocked = User::factory()->create(['status' => UserStatusEnum::BLOCK]);
    linkSheet($blocked);

    savedWords('English,Persian,win,برنده شدن');

    $this->artisan('words:sync-google-translate')->assertSuccessful();

    expect($this->user->decks()->sole()->cards()->count())->toBe(1)
        ->and($blocked->decks()->count())->toBe(0);
});

it('shows the section on the dashboard', function (): void {
    $this->actingAs($this->user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Words saved in Google Translate')
        ->assertSee('Link and sync');

    linkSheet($this->user);

    $this->actingAs($this->user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Sync now')
        ->assertSee('Not synced yet');
});
