<?php

declare(strict_types=1);

it('ships a real ICO favicon, not a renamed SVG', function (): void {
    // favicon.ico used to be the SVG logo under an .ico name: iOS could not decode
    // it, so bookmarks and the share sheet showed no icon at all.
    $bytes = file_get_contents(public_path('favicon.ico'));

    expect(substr($bytes, 0, 4))->toBe("\x00\x00\x01\x00");
});

it('ships every icon the head and manifest point at', function (): void {
    $manifest = json_decode(file_get_contents(public_path('manifest.json')), true);

    expect($manifest['name'])->toBe('MemFlash')
        ->and($manifest['short_name'])->toBe('MemFlash');

    $paths = [
        ...array_map(fn (array $icon): string => ltrim($icon['src'], '/'), $manifest['icons']),
        'icons/apple-touch-icon.png',
        'icons/favicon-32.png',
        // iOS and link-preview fetchers also probe the site root.
        'apple-touch-icon.png',
        'apple-touch-icon-precomposed.png',
    ];

    foreach ($paths as $path) {
        expect(getimagesize(public_path($path)))->not->toBeFalse($path);
    }
});

it('gives iOS an icon and a short name on the pages people save', function (string $uri): void {
    $html = $this->get($uri)->assertOk()->getContent();

    expect($html)
        ->toContain('rel="apple-touch-icon"')
        ->toContain('<meta name="apple-mobile-web-app-title" content="MemFlash">')
        ->toContain('rel="icon" href="' . asset('favicon.ico') . '?v=')
        ->toContain('id="pwa-install"')
        // Bookmarks and shares take the page title.
        ->toMatch('#<title>MemFlash[^<]{0,30}</title>#u')
        ->not->toContain('type="image/svg+xml"');
})->with(['/', '/login']);

it('gives link previews an image they can render', function (): void {
    $this->get('/')
        ->assertOk()
        ->assertSee('<meta property="og:image" content="' . asset('icons/icon-512.png') . '">', false);
});
