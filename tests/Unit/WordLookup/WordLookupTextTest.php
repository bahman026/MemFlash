<?php

declare(strict_types=1);

use App\Services\WordLookup\Direction;
use App\Services\WordLookup\Language;
use App\Services\WordLookup\LookupResult;
use App\Services\WordLookup\Sense;
use App\Services\WordLookup\Text;

it('tells Persian from English by script', function (string $text, Language $expected): void {
    expect(Language::detect($text))->toBe($expected);
})->with([
    'English word' => ['ahead', Language::English],
    'English phrase' => ['look forward to', Language::English],
    'Persian word' => ['کتاب', Language::Persian],
    'Persian with ZWNJ' => ['می‌روم', Language::Persian],
    'Persian typed with Arabic letters' => ['اذيت', Language::Persian],
]);

it('strips the sentence punctuation a translation memory leaves on a word', function (): void {
    expect(Text::clean('به جلو.'))->toBe('به جلو')
        ->and(Text::clean('  اذیت کردن . '))->toBe('اذیت کردن')
        ->and(Text::clean('«پیش رو»'))->toBe('پیش رو')
        ->and(Text::clean('چرا؟'))->toBe('چرا')
        ->and(Text::clean("look\u{00A0} forward   to"))->toBe('look forward to');
});

it('writes Persian with Persian letters, not their Arabic look-alikes', function (): void {
    // Arabic yeh (U+064A) and kaf (U+0643) look the same but compare unequal.
    expect(Text::clean("اذ\u{064A}ت"))->toBe("اذ\u{06CC}ت")
        ->and(Text::clean("\u{0643}تاب"))->toBe("\u{06A9}تاب");
});

it('keeps the zero-width non-joiner that Persian spelling depends on', function (): void {
    expect(Text::clean("می\u{200C}روم."))->toBe("می\u{200C}روم");
});

it('does not cut bytes off Persian letters when trimming quotes', function (): void {
    // trim() is byte-based, and curly quotes share bytes with Persian letters.
    // Every word here must survive unchanged and stay valid UTF-8.
    foreach (['پیروز شدن', 'کتاب', 'گرفتن', 'ژاکت', 'چشم انتظار'] as $word) {
        expect(Text::clean("“{$word}”"))->toBe($word)
            ->and(mb_check_encoding(Text::clean($word), 'UTF-8'))->toBeTrue();
    }
});

it('undoes sentence case in English translations but keeps acronyms', function (): void {
    expect(Text::cleanTranslation('Book', Language::English))->toBe('book')
        ->and(Text::cleanTranslation('To win.', Language::English))->toBe('to win')
        ->and(Text::cleanTranslation('TV', Language::English))->toBe('TV')
        ->and(Text::cleanTranslation('it&#39;s', Language::English))->toBe("it's");
});

it('keeps translation candidates in the target language, once each, best first', function (): void {
    $texts = Text::translations(
        ['به جلو.', 'به جلو .', 'ahead', 'MYMEMORY WARNING: YOU USED ALL AVAILABLE FREE TRANSLATIONS', 'جلو', null, '', 'پیش', 'رو'],
        Language::Persian,
        3,
    );

    expect($texts)->toBe(['به جلو', 'جلو', 'پیش']);
});

it('turns plain text out of a definition marked up with links', function (): void {
    $html = 'At an earlier time; <a rel="mw:WikiLink" href="/wiki/beforehand">beforehand</a>; in&nbsp;advance.';

    expect(Text::fromHtml($html))->toBe('At an earlier time; beforehand; in advance.');
});

it('reads the direction from Google Translate-style sl and tl', function (mixed $sl, mixed $tl, ?Direction $expected): void {
    expect(Direction::fromQuery($sl, $tl))->toBe($expected);
})->with([
    'sl=en&tl=fa' => ['en', 'fa', Direction::EnglishToPersian],
    'sl=fa&tl=en' => ['fa', 'en', Direction::PersianToEnglish],
    'sl=en&tl=en, the English dictionary' => ['en', 'en', Direction::EnglishToEnglish],
    'sl=en alone goes to Persian' => ['en', null, Direction::EnglishToPersian],
    'sl=fa alone' => ['fa', null, Direction::PersianToEnglish],
    'tl=fa alone means from English' => [null, 'fa', Direction::EnglishToPersian],
    'tl=en alone means from Persian' => [null, 'en', Direction::PersianToEnglish],
    'Persian to Persian is not offered' => ['fa', 'fa', Direction::PersianToEnglish],
    'upper case' => ['EN', 'EN', Direction::EnglishToEnglish],
    'sl=auto detects, whatever tl says' => ['auto', 'en', null],
    'neither' => [null, null, null],
    'a language it does not know' => ['de', null, null],
    'not a string' => [['en'], null, null],
]);

it('detects the usual direction from the script', function (): void {
    expect(Direction::detect('hello'))->toBe(Direction::EnglishToPersian)
        ->and(Direction::detect('سلام'))->toBe(Direction::PersianToEnglish);
});

it('suggests English on the front and Persian on the back for an English word', function (): void {
    $result = new LookupResult(
        query: 'ahead',
        direction: Direction::EnglishToPersian,
        senses: [new Sense('In or for the future.', 'adverb', 'There may be tough times ahead.')],
        translations: ['پیش رو', 'به جلو'],
        phonetic: '/əˈhɛd/',
    );

    expect($result->card())->toBe([
        'front' => 'ahead',
        'back' => 'پیش رو',
        'description' => 'adverb: In or for the future. Example: There may be tough times ahead.',
        'pronunciation' => '/əˈhɛd/',
    ])->and($result->word())->toBe('ahead')
        ->and($result->isComplete())->toBeTrue();
});

it('suggests English on the front and Persian on the back for a Persian word too', function (): void {
    $result = new LookupResult(query: 'کتاب', direction: Direction::PersianToEnglish, translations: ['book', 'notebook']);

    expect($result->card())->toBe(['front' => 'book', 'back' => 'کتاب', 'description' => null, 'pronunciation' => null])
        ->and($result->word())->toBe('book')
        ->and($result->isComplete())->toBeTrue();
});

it('puts the English meaning on the back when there is no Persian translation', function (): void {
    $result = new LookupResult(query: 'ahead', direction: Direction::EnglishToPersian, senses: [new Sense('In or for the future.')]);

    expect($result->card()['back'])->toBe('In or for the future.')
        ->and($result->isComplete())->toBeFalse();
});

it('explains an English word in English, the meaning being the answer', function (): void {
    $result = new LookupResult(
        query: 'hello',
        direction: Direction::EnglishToEnglish,
        senses: [
            new Sense('A greeting (salutation) said when meeting someone or acknowledging someone’s arrival or presence.', 'interjection', 'Hello, everyone.'),
            new Sense('An expression of puzzlement or discovery.', 'interjection'),
        ],
    );

    expect($result->card())->toBe([
        'front' => 'hello',
        'back' => 'A greeting (salutation) said when meeting someone or acknowledging someone’s arrival or presence.',
        'description' => 'Example: Hello, everyone.',
        'pronunciation' => null,
    ])->and($result->isComplete())->toBeTrue()
        ->and($result->toArray()['target'])->toBe('en');

    // Picking another meaning puts that meaning on the back.
    expect($result->toArray()['senses'][1])->toMatchArray([
        'back' => 'An expression of puzzlement or discovery.',
        'note' => null,
    ]);
});

it('keeps the chosen translation on the back when another meaning is picked', function (): void {
    $result = new LookupResult(
        query: 'ahead',
        direction: Direction::EnglishToPersian,
        senses: [new Sense('In or for the future.'), new Sense('To a later time.', 'adverb')],
        translations: ['پیش رو'],
    );

    expect($result->toArray()['senses'][1])->toMatchArray(['back' => null, 'note' => 'adverb: To a later time.']);
});

it('survives the trip through the cache unchanged', function (): void {
    $result = new LookupResult(
        query: 'ahead',
        direction: Direction::EnglishToPersian,
        senses: [new Sense('In or for the future.', 'adverb', 'Tough times ahead.'), new Sense('To a later time.')],
        translations: ['پیش رو'],
        phonetic: '/əˈhɛd/',
        sources: ['wiktionary', 'mymemory'],
    );

    expect(LookupResult::fromArray($result->toArray()))->toEqual($result);

    $dictionary = new LookupResult(query: 'hello', direction: Direction::EnglishToEnglish, senses: [new Sense('A greeting.')]);

    expect(LookupResult::fromArray($dictionary->toArray()))->toEqual($dictionary);
});
