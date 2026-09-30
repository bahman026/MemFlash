<?php

declare(strict_types=1);

use App\Models\Card;
use App\Models\Deck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

/**
 * @param  list<list<string|int|null>>  $rows
 */
function xlsxUpload(array $rows): UploadedFile
{
    $spreadsheet = new Spreadsheet;
    $spreadsheet->getActiveSheet()->fromArray($rows);

    $path = tempnam(sys_get_temp_dir(), 'deck') . '.xlsx';
    (new Xlsx($spreadsheet))->save($path);

    return new UploadedFile($path, 'vocab.xlsx', null, null, true);
}

function csvUpload(string $contents): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'deck') . '.csv';
    file_put_contents($path, $contents);

    return new UploadedFile($path, 'vocab.csv', 'text/csv', null, true);
}

it('creates a deck from a sheet with named columns and a description', function (): void {
    // The layout of the vocabulary workbooks: a "#" counter, the word, its
    // meaning, an example, and a footnote in a single cell under the table.
    $file = xlsxUpload([
        ['#', 'English Word / Phrase', 'English Meaning', 'English Example'],
        [1, 'to annoy', 'to make someone slightly angry', 'Loud phone calls annoy me.'],
        [2, 'to infuriate', 'to make someone extremely angry', 'It infuriates me when drivers tailgate.'],
        [3, 'to thrill', 'to make someone feel excited', null],
        [null, null, null, null],
        [null, 'Examples are original sentences written for practice.', null, null],
    ]);

    $this->actingAs($this->user)->post(route('decks.store'), [
        'import_mode' => 'new',
        'name' => 'Vocabulary 5B',
        'new_cards_per_day' => 20,
        'file' => $file,
    ])->assertRedirect(route('dashboard'))->assertSessionHasNoErrors();

    $deck = Deck::query()->where('user_id', $this->user->id)->sole();
    $cards = $deck->cards()->orderBy('id')->get();

    expect($deck->name)->toBe('Vocabulary 5B')
        ->and($cards)->toHaveCount(3)
        ->and($cards[0]->only(['front', 'back', 'description']))->toBe([
            'front' => 'to annoy',
            'back' => 'to make someone slightly angry',
            'description' => 'Loud phone calls annoy me.',
        ])
        ->and($cards[2]->description)->toBeNull();
});

it('matches columns by header regardless of their order', function (): void {
    $file = xlsxUpload([
        ['Vocabulary 7A'],
        ['Example', 'Meaning', 'Word'],
        ['Cooking together is a pleasure.', 'things that give enjoyment', 'pleasures'],
    ]);

    $this->actingAs($this->user)->post(route('decks.store'), [
        'import_mode' => 'new',
        'name' => 'Reordered',
        'new_cards_per_day' => 20,
        'file' => $file,
    ])->assertSessionHasNoErrors();

    expect(Card::query()->sole()->only(['front', 'back', 'description']))->toBe([
        'front' => 'pleasures',
        'back' => 'things that give enjoyment',
        'description' => 'Cooking together is a pleasure.',
    ]);
});

it('keeps reading headerless two-column files as word and meaning', function (): void {
    // Every row the same width: libmagic only calls a file text/csv when the rows
    // agree, and the upload's `mimes:csv` rule rejects plain text.
    $file = csvUpload("critical thinking,تفکر انتقادی,\nwin,پیروز شدن,We won the match.\n");

    $this->actingAs($this->user)->post(route('decks.store'), [
        'import_mode' => 'new',
        'name' => 'Persian',
        'new_cards_per_day' => 20,
        'file' => $file,
    ])->assertSessionHasNoErrors();

    $cards = Card::query()->orderBy('id')->get(['front', 'back', 'description'])->toArray();

    expect($cards)->toBe([
        ['front' => 'critical thinking', 'back' => 'تفکر انتقادی', 'description' => null],
        ['front' => 'win', 'back' => 'پیروز شدن', 'description' => 'We won the match.'],
    ]);
});

it('re-imports its own CSV export without turning the header into a card', function (): void {
    // The export starts with a UTF-8 byte-order mark, which trim() leaves on
    // the first cell and which used to stop the header being recognised.
    $file = csvUpload("\xEF\xBB\xBFFront,Back,Description\nwin,پیروز شدن,We won.\n");

    $this->actingAs($this->user)->post(route('decks.store'), [
        'import_mode' => 'new',
        'name' => 'Round trip',
        'new_cards_per_day' => 20,
        'file' => $file,
    ])->assertSessionHasNoErrors();

    expect(Card::query()->pluck('front')->all())->toBe(['win']);
});

it('updates descriptions on import into an existing deck, but never blanks one', function (): void {
    $deck = Deck::factory()->for($this->user)->create();
    $kept = Card::factory()->for($deck)->create(['front' => 'win', 'back' => 'old', 'description' => 'Keep me.']);
    $changed = Card::factory()->for($deck)->create(['front' => 'lose', 'back' => 'old', 'description' => 'Old example.']);

    $file = xlsxUpload([
        ['Word', 'Meaning', 'Example'],
        ['win', 'to be first', null],
        ['lose', 'to fail to win', 'We lost the final.'],
    ]);

    $this->actingAs($this->user)->post(route('decks.store'), [
        'import_mode' => 'existing',
        'existing_deck_id' => $deck->id,
        'file' => $file,
    ])->assertSessionHasNoErrors();

    expect($kept->fresh()->only(['back', 'description']))->toBe(['back' => 'to be first', 'description' => 'Keep me.'])
        ->and($changed->fresh()->only(['back', 'description']))->toBe(['back' => 'to fail to win', 'description' => 'We lost the final.']);
});

it('sends the description to the study screen', function (): void {
    $deck = Deck::factory()->for($this->user)->create();
    Card::factory()->for($deck)->create(['description' => 'Loud phone calls annoy me.']);

    $this->actingAs($this->user)
        ->getJson(route('study.cards', $deck))
        ->assertOk()
        ->assertJsonPath('cards.0.description', 'Loud phone calls annoy me.');
});

it('saves a description typed into the card form', function (): void {
    $deck = Deck::factory()->for($this->user)->create();

    $this->actingAs($this->user)->post(route('cards.store', $deck), [
        'front' => 'to amaze',
        'back' => 'to surprise someone greatly',
        'description' => 'It amazes me how fast she codes.',
    ])->assertSessionHasNoErrors();

    expect(Card::query()->sole()->description)->toBe('It amazes me how fast she codes.');
});
