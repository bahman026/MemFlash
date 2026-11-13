<?php

declare(strict_types=1);

use App\Services\WordLookup\Direction;
use App\Services\WordLookup\VocabBank;
use App\Services\WordLookup\WordLookupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

/**
 * kaikki.org entries, trimmed to the fields the importer reads.
 *
 * @return list<array<string, mixed>>
 */
function kaikkiEntries(): array
{
    return [
        ['word' => 'خنجر', 'lang_code' => 'fa', 'pos' => 'noun', 'senses' => [['glosses' => ['dagger']]]],
        ['word' => 'دشنه', 'lang_code' => 'fa', 'pos' => 'noun', 'senses' => [['glosses' => ['dagger, poniard']]]],
        ['word' => 'کتاب', 'lang_code' => 'fa', 'pos' => 'noun', 'senses' => [
            ['glosses' => ['book']],
            ['glosses' => ['plural of کاتب (kātib)'], 'tags' => ['form-of', 'plural'], 'form_of' => [['word' => 'کاتب']]],
        ], 'derived' => [['word' => 'کتابخانه'], ['word' => 'کتابدار']]],
        ['word' => 'نسک', 'lang_code' => 'fa', 'pos' => 'noun', 'senses' => [['glosses' => ['book'], 'tags' => ['archaic']]]],
        ['word' => 'گو', 'lang_code' => 'fa', 'pos' => 'noun', 'senses' => [['glosses' => ['go (board game)']]]],
        ['word' => 'رفتن', 'lang_code' => 'fa', 'pos' => 'verb', 'senses' => [['glosses' => ['to go']], ['glosses' => ['to leave']]]],
        ['word' => 'هیجان‌زده', 'lang_code' => 'fa', 'pos' => 'adj', 'senses' => [['glosses' => ['excited']]]],
        ['word' => 'تهران', 'lang_code' => 'fa', 'pos' => 'name', 'senses' => [['glosses' => ['Tehran (the capital of Iran)']]]],
        ['word' => 'آب', 'lang_code' => 'fa', 'pos' => 'noun', 'senses' => [['glosses' => ['the clear liquid that falls as rain and fills rivers and seas']]]],
    ];
}

function kaikkiFile(): string
{
    $path = tempnam(sys_get_temp_dir(), 'kaikki-') . '.jsonl.gz';
    $lines = implode("\n", array_map(fn (array $e): string => json_encode($e, JSON_UNESCAPED_UNICODE), kaikkiEntries())) . "\nnot json\n";
    file_put_contents($path, gzencode($lines));

    return $path;
}

it('imports both directions, best answer first', function (): void {
    $bank = app(VocabBank::class);

    expect($bank->import(kaikkiFile()))->toBe(8);

    // Archaic Persian (نسک) and plural forms are left out.
    expect($bank->translate('book', \App\Services\WordLookup\Language::English, 6))->toBe(['کتاب'])
        // The only meaning beats one of two.
        ->and($bank->translate('Dagger', \App\Services\WordLookup\Language::English, 6))->toBe(['خنجر', 'دشنه'])
        // "go (board game)" is not "go".
        ->and($bank->translate('to go', \App\Services\WordLookup\Language::English, 6))->toBe(['رفتن', 'گو'])
        ->and($bank->translate('خنجر', \App\Services\WordLookup\Language::Persian, 6))->toBe(['dagger'])
        ->and($bank->translate('هیجان زده', \App\Services\WordLookup\Language::Persian, 6))->toBe(['excited'])
        // Names and definitions are not translations.
        ->and($bank->translate('tehran', \App\Services\WordLookup\Language::English, 6))->toBe([])
        ->and($bank->translate('آب', \App\Services\WordLookup\Language::Persian, 6))->toBe([]);
});

it('replaces the bank on a second import', function (): void {
    $bank = app(VocabBank::class);
    $bank->import(kaikkiFile());
    $bank->import(kaikkiFile());

    expect(DB::table(VocabBank::TABLE)->count())->toBe(8);
});

it('downloads the dictionary when no file is given', function (): void {
    Http::fake([VocabBank::SOURCE => Http::response(file_get_contents(kaikkiFile()))]);

    $this->artisan('words:import-vocab-bank')
        ->expectsOutputToContain('8 English ↔ Persian pairs')
        ->assertSuccessful();

    expect(DB::table(VocabBank::TABLE)->where('english_key', 'dagger')->count())->toBe(2);
});

it('answers English → Persian from the bank before any outside translator', function (): void {
    Http::preventStrayRequests();
    Http::fake(['en.wiktionary.org/*' => Http::response([], 404)]);

    config([
        'services.word_lookup.definitions' => ['wiktionary'],
        'services.word_lookup.translators' => ['vocabbank', 'mymemory'],
    ]);
    app()->forgetInstance(WordLookupService::class);

    app(VocabBank::class)->import(kaikkiFile());

    $result = app(WordLookupService::class)->lookup('dagger', Direction::EnglishToPersian)->toArray();

    expect($result['translations'])->toBe(['خنجر', 'دشنه'])
        ->and($result['card']['back'])->toBe('خنجر');
    Http::assertNotSent(fn ($request): bool => str_contains($request->url(), 'mymemory'));
});
