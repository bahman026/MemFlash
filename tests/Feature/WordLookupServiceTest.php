<?php

declare(strict_types=1);

use App\Services\WordLookup\Direction;
use App\Services\WordLookup\LookupResult;
use App\Services\WordLookup\WordLookupService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

beforeEach(function (): void {
    // Every provider is faked; a request that is not would reach the internet.
    Http::preventStrayRequests();

    config([
        'services.word_lookup.definitions' => ['wiktionary', 'datamuse'],
        'services.word_lookup.translators' => ['google', 'mymemory'],
        'services.word_lookup.providers.google.key' => null,
        'services.word_lookup.providers.mymemory.email' => null,
    ]);
});

/**
 * Trimmed from a real en.wiktionary.org /page/definition/ahead response.
 *
 * @return array<string, mixed>
 */
function wiktionaryAhead(): array
{
    return [
        'en' => [[
            'partOfSpeech' => 'Adverb',
            'language' => 'English',
            'definitions' => [
                [
                    'definition' => 'At or towards the <a rel="mw:WikiLink" href="/wiki/front" title="front">front</a>; in the direction one is facing or moving.',
                    'examples' => ['The island was directly <b>ahead</b>.', 'Keep going straight <b>ahead</b>.'],
                ],
                ['definition' => ''],
                ['definition' => 'In or for the future.', 'examples' => ['There may be tough times <b>ahead</b>.']],
                ['definition' => 'To a later time.'],
                ['definition' => 'At an earlier time; <a href="/wiki/beforehand">beforehand</a>.'],
                ['definition' => 'To an earlier time.'],
            ],
        ]],
    ];
}

/**
 * The shape of an api.mymemory.translated.net/get response.
 *
 * @param  list<array{0: string, 1: float}>  $matches  [translation, match]
 * @return array<string, mixed>
 */
function myMemory(string $translated, array $matches = [], int $status = 200): array
{
    return [
        'responseData' => ['translatedText' => $translated, 'match' => 0.99],
        'responseStatus' => $status,
        'quotaFinished' => $status === 429,
        'matches' => array_map(fn (array $m): array => ['translation' => $m[0], 'match' => $m[1]], $matches),
    ];
}

function lookUp(string $word, ?Direction $direction = null): LookupResult
{
    return app(WordLookupService::class)->lookup($word, $direction);
}

it('explains an English word in English and translates it into Persian', function (): void {
    Http::fake([
        'en.wiktionary.org/*' => Http::response(wiktionaryAhead()),
        'api.mymemory.translated.net/*' => Http::response(myMemory('به جلو.', [
            ['به جلو.', 0.99],
            ['پيش رو', 0.9],
            ['قلقلکی است.', 0.5], // a fuzzy hit on another sentence
        ])),
    ]);

    $result = lookUp('  ahead ');

    expect($result->direction)->toBe(Direction::EnglishToPersian)
        ->and($result->query)->toBe('ahead')
        ->and($result->translations)->toBe(['به جلو', 'پیش رو'])
        ->and($result->senses)->toHaveCount(WordLookupService::MAX_SENSES)
        ->and($result->senses[0]->definition)->toBe('At or towards the front; in the direction one is facing or moving.')
        ->and($result->senses[0]->partOfSpeech)->toBe('adverb')
        ->and($result->senses[0]->example)->toBe('The island was directly ahead.')
        ->and($result->senses[1]->definition)->toBe('In or for the future.')
        ->and($result->sources)->toBe(['wiktionary', 'mymemory']);

    Http::assertSent(fn (Request $request): bool => str_starts_with($request->url(), 'https://api.mymemory.translated.net/get')
        && $request['langpair'] === 'en|fa'
        && $request['q'] === 'ahead');

    // The first definition source answered, so the fallback was never asked.
    Http::assertNotSent(fn (Request $request): bool => str_contains($request->url(), 'datamuse'));
});

it('gives only an English equivalent for a Persian word', function (): void {
    Http::fake([
        'api.mymemory.translated.net/*' => Http::response(myMemory('Book', [['book.', 0.98], ['notebook', 0.85]])),
    ]);

    $result = lookUp('كتاب'); // typed with an Arabic kaf

    expect($result->direction)->toBe(Direction::PersianToEnglish)
        ->and($result->query)->toBe('کتاب')
        ->and($result->translations)->toBe(['book', 'notebook'])
        ->and($result->senses)->toBe([])
        ->and($result->card()['front'])->toBe('book')
        ->and($result->card()['back'])->toBe('کتاب');

    Http::assertSent(fn (Request $request): bool => ($request['langpair'] ?? null) === 'fa|en');
    Http::assertNotSent(fn (Request $request): bool => str_contains($request->url(), 'wiktionary'));
});

it('takes the direction it is given instead of detecting it, and caches each apart', function (): void {
    Http::fake([
        'en.wiktionary.org/*' => Http::response(wiktionaryAhead()),
        'api.mymemory.translated.net/*' => fn (Request $request) => Http::response(
            $request['langpair'] === 'fa|en' ? myMemory('Later') : myMemory('بعدا'),
        ),
    ]);

    // As on Google Translate with sl=fa: asked for, so done, even on Latin text.
    $persian = lookUp('later', Direction::PersianToEnglish);
    $english = lookUp('later');

    expect($persian->direction)->toBe(Direction::PersianToEnglish)
        ->and($persian->translations)->toBe(['later'])
        ->and($persian->senses)->toBe([])
        ->and($english->direction)->toBe(Direction::EnglishToPersian)
        ->and($english->translations)->toBe(['بعدا']);

    Http::assertSent(fn (Request $request): bool => ($request['langpair'] ?? null) === 'fa|en');
    Http::assertSent(fn (Request $request): bool => ($request['langpair'] ?? null) === 'en|fa');
});

it('explains an English word in English without asking a translator', function (): void {
    Http::fake([
        'en.wiktionary.org/*' => Http::response(['en' => [[
            'partOfSpeech' => 'Interjection',
            'definitions' => [[
                'definition' => 'A <a href="/wiki/greeting">greeting</a> (<a href="/wiki/salutation">salutation</a>) said when meeting someone or acknowledging someone’s arrival or presence.',
                'examples' => ['<b>Hello</b>, everyone.'],
            ]],
        ]]]),
    ]);

    $result = lookUp('hello', Direction::EnglishToEnglish);

    expect($result->direction)->toBe(Direction::EnglishToEnglish)
        ->and($result->translations)->toBe([])
        ->and($result->sources)->toBe(['wiktionary'])
        ->and($result->card()['back'])->toBe('A greeting (salutation) said when meeting someone or acknowledging someone’s arrival or presence.')
        ->and($result->card()['description'])->toBe('Example: Hello, everyone.');

    // Complete without a translation, so it is cached for good.
    lookUp('hello', Direction::EnglishToEnglish);

    Http::assertSentCount(1);
    Http::assertNotSent(fn (Request $request): bool => str_contains($request->url(), 'mymemory'));
});

it('asks the next definition source when the first has no entry', function (): void {
    Http::fake([
        'en.wiktionary.org/*' => Http::response(['title' => 'Not found.'], 404),
        'api.datamuse.com/*' => Http::response([[
            'word' => 'ahead',
            'score' => 153082,
            'tags' => ['adv', 'pron:AH0 HH EH1 D ', 'ipa_pron:ʌhˈɛd'],
            'defs' => ["adv\tAt or towards the front. ", "adv\tIn or for the future. "],
        ]]),
        'api.mymemory.translated.net/*' => Http::response(myMemory('به جلو')),
    ]);

    $result = lookUp('ahead');

    expect($result->senses[0]->definition)->toBe('At or towards the front.')
        ->and($result->senses[0]->partOfSpeech)->toBe('adverb')
        ->and($result->phonetic)->toBe('/ʌhˈɛd/')
        ->and($result->sources)->toBe(['datamuse', 'mymemory']);
});

it('does not take a different word that Datamuse matched as the definition', function (): void {
    Http::fake([
        'en.wiktionary.org/*' => Http::response([], 404),
        'api.datamuse.com/*' => Http::response([['word' => 'aheads', 'defs' => ["n\tSomething else."]]]),
        'api.mymemory.translated.net/*' => Http::response(myMemory('به جلو')),
    ]);

    expect(lookUp('ahead')->senses)->toBe([]);
});

it('tries the lower-case Wiktionary page when the word was typed capitalized', function (): void {
    Http::fake([
        'en.wiktionary.org/api/rest_v1/page/definition/Ahead' => Http::response([], 404),
        'en.wiktionary.org/api/rest_v1/page/definition/ahead' => Http::response(wiktionaryAhead()),
        'api.mymemory.translated.net/*' => Http::response(myMemory('به جلو')),
    ]);

    expect(lookUp('Ahead')->senses[0]->partOfSpeech)->toBe('adverb');
});

it('looks phrases up by their Wiktionary title', function (): void {
    Http::fake([
        'en.wiktionary.org/api/rest_v1/page/definition/look_forward_to' => Http::response(['en' => [[
            'partOfSpeech' => 'Verb',
            'definitions' => [['definition' => 'To anticipate with pleasure.']],
        ]]]),
        'api.mymemory.translated.net/*' => Http::response(myMemory('چشم انتظار')),
    ]);

    expect(lookUp('look forward to')->senses[0]->definition)->toBe('To anticipate with pleasure.');
});

it('skips a provider that fails and logs why', function (): void {
    Log::spy();

    Http::fake([
        'en.wiktionary.org/*' => Http::failedConnection('timed out'),
        'api.datamuse.com/*' => Http::response([['word' => 'ahead', 'defs' => ["adv\tIn or for the future."]]]),
        'api.mymemory.translated.net/*' => Http::response(myMemory('به جلو')),
    ]);

    expect(lookUp('ahead')->senses[0]->definition)->toBe('In or for the future.');

    Log::shouldHaveReceived('warning')->withArgs(fn (string $message, array $context): bool => $message === 'Word lookup provider failed'
        && str_contains($context['provider'], 'WiktionaryDefinitions'));
});

it('treats a spent MyMemory quota as no translation, not as one', function (): void {
    Http::fake([
        'en.wiktionary.org/*' => Http::response(wiktionaryAhead()),
        // HTTP 200, with the warning where the translation goes.
        'api.mymemory.translated.net/*' => Http::response(myMemory('MYMEMORY WARNING: YOU USED ALL AVAILABLE FREE TRANSLATIONS FOR TODAY', status: 429)),
    ]);

    $result = lookUp('ahead');

    expect($result->translations)->toBe([])
        ->and($result->card()['back'])->toBe('At or towards the front; in the direction one is facing or moving.');
});

it('asks Google first once it has an API key, and sends the key in a header', function (): void {
    config(['services.word_lookup.providers.google.key' => 'test-key']);

    Http::fake([
        'en.wiktionary.org/*' => Http::response(wiktionaryAhead()),
        'translation.googleapis.com/*' => Http::response(['data' => ['translations' => [['translatedText' => 'پیش رو']]]]),
    ]);

    expect(lookUp('ahead')->translations)->toBe(['پیش رو']);

    Http::assertSent(fn (Request $request): bool => str_starts_with($request->url(), 'https://translation.googleapis.com/')
        && $request->hasHeader('X-Goog-Api-Key', 'test-key')
        && ! str_contains($request->url(), 'test-key')
        && $request['source'] === 'en'
        && $request['target'] === 'fa');
    Http::assertNotSent(fn (Request $request): bool => str_contains($request->url(), 'mymemory'));
});

it('remembers a complete answer instead of asking the services again', function (): void {
    Http::fake([
        'en.wiktionary.org/*' => Http::response(wiktionaryAhead()),
        'api.mymemory.translated.net/*' => Http::response(myMemory('به جلو')),
    ]);

    lookUp('ahead');
    $this->travel(29)->days();
    $again = lookUp('ahead');

    expect($again->translations)->toBe(['به جلو'])
        ->and($again->senses[0]->partOfSpeech)->toBe('adverb');
    Http::assertSentCount(2);
});

it('asks again soon when part of the answer was missing', function (): void {
    // A gap may be an outage or a spent quota, not an unknown word, so it is
    // not remembered for a month.
    Http::fakeSequence('api.mymemory.translated.net/*')
        ->push(myMemory('', status: 429))
        ->push(myMemory('به جلو'));
    Http::fake(['en.wiktionary.org/*' => Http::response(wiktionaryAhead())]);

    expect(lookUp('ahead')->translations)->toBe([]);

    $this->travel(11)->minutes();

    expect(lookUp('ahead')->translations)->toBe(['به جلو']);
});

it('can run with no definition source at all', function (): void {
    config(['services.word_lookup.definitions' => []]);

    Http::fake(['api.mymemory.translated.net/*' => Http::response(myMemory('به جلو'))]);

    $result = lookUp('ahead');

    expect($result->senses)->toBe([])
        ->and($result->translations)->toBe(['به جلو']);
});

it('refuses a provider name it does not know', function (): void {
    config(['services.word_lookup.translators' => ['mymemroy']]);

    app(WordLookupService::class);
})->throws(InvalidArgumentException::class, 'Unknown word lookup provider [mymemroy]');
