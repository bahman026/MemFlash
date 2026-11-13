<?php

declare(strict_types=1);

use App\Models\Card;
use App\Models\Deck;
use App\Models\User;
use App\Services\WordListService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the download page to anyone', function (): void {
    $manifest = json_decode(file_get_contents(base_path('extension/manifest.json')), true);

    $this->get(route('extension'))
        ->assertOk()
        ->assertSee('MemFlash for Chrome')
        ->assertSee(route('extension.download'), false)
        ->assertSee('Version ' . $manifest['version']);
});

it('names the extension MemFlash and drops the Google Drive sign-in', function (): void {
    $manifest = json_decode(file_get_contents(base_path('extension/manifest.json')), true);

    expect($manifest['name'])->toBe('MemFlash')
        ->and($manifest)->not->toHaveKey('oauth2')
        ->and($manifest)->not->toHaveKey('key')
        ->and($manifest['permissions'])->not->toContain('identity')
        ->and($manifest['host_permissions'])->toContain('https://memflash.ir/*')
        ->and(file_exists(base_path('extension/key.pem')))->toBeFalse();
});

it('zips the extension folder for download', function (): void {
    $response = $this->get(route('extension.download'))->assertOk();

    $path = $response->baseResponse->getFile()->getPathname();
    $zip = new ZipArchive;
    expect($zip->open($path))->toBeTrue();

    $names = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $names[] = $zip->getNameIndex($i);
    }
    $manifest = json_decode((string) $zip->getFromName('memflash-extension/manifest.json'), true);
    $zip->close();

    expect($names)->toContain('memflash-extension/manifest.json', 'memflash-extension/background.js', 'memflash-extension/icons/icon128.png')
        ->and($manifest['name'])->toBe('MemFlash')
        ->and($response->headers->get('content-disposition'))->toContain('memflash-extension-' . $manifest['version'] . '.zip');
});

it('tells a signed-out extension so instead of redirecting', function (): void {
    $this->getJson(route('extension.session'))
        ->assertOk()
        ->assertExactJson(['signed_in' => false]);
});

it('gives the extension the lists, a CSRF token and the newest words', function (): void {
    $user = User::factory()->create();
    $list = Deck::factory()->for($user)->create(['name' => 'Reading']);
    Card::factory()->for($list)->create(['front' => 'old', 'back' => 'کهنه']);
    Card::factory()->for($list)->create(['front' => 'new', 'back' => 'نو']);
    $user->setPreference('word_list_id', $list->id);

    $this->actingAs($user)
        ->getJson(route('extension.session'))
        ->assertOk()
        ->assertJsonPath('signed_in', true)
        ->assertJsonPath('user.email', $user->email)
        ->assertJsonPath('list_id', $list->id)
        ->assertJsonPath('lists.0.name', 'Reading')
        ->assertJsonPath('words.0.front', 'new')
        ->assertJsonPath('words.1.front', 'old')
        ->assertJsonPath('default_list_name', WordListService::DEFAULT_LIST_NAME)
        ->assertJsonStructure(['csrf']);
});

it('lists the extension page in the sitemap and links it from the profile menu', function (): void {
    $this->get('/sitemap.xml')->assertSee(route('extension'), false);

    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertSee(route('extension'), false);
});
