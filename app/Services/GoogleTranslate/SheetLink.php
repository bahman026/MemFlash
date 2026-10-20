<?php

declare(strict_types=1);

namespace App\Services\GoogleTranslate;

/**
 * A Google Sheets link as the user pastes it, and the CSV export of that sheet.
 *
 * Takes the address of the sheet itself (…/spreadsheets/d/{id}/edit#gid=0),
 * which works once it is shared as "anyone with the link can view", and the
 * address of a sheet published to the web (…/spreadsheets/d/e/{id}/pubhtml).
 *
 * The CSV address is rebuilt from the sheet's id alone, never from the pasted
 * text, so a link can only ever make the server fetch from docs.google.com.
 */
final class SheetLink
{
    private function __construct(
        public readonly string $url,
        public readonly string $csvUrl,
    ) {}

    public static function parse(string $url): ?self
    {
        $url = trim($url);
        $parts = parse_url($url);

        if (! is_array($parts) || ($parts['host'] ?? null) !== 'docs.google.com' || ! in_array($parts['scheme'] ?? null, ['http', 'https'], true)) {
            return null;
        }

        $path = $parts['path'] ?? '';

        // The tab: ?gid= or #gid=. Without one Google exports the first tab.
        $gid = null;
        foreach ([$parts['query'] ?? '', $parts['fragment'] ?? ''] as $params) {
            if (preg_match('/(?:^|&)gid=(\d+)/', $params, $match) === 1) {
                $gid = $match[1];
            }
        }
        $tab = $gid === null ? '' : "&gid={$gid}";

        if (preg_match('#^/spreadsheets/d/e/([\w-]+)#', $path, $match) === 1) {
            return new self($url, "https://docs.google.com/spreadsheets/d/e/{$match[1]}/pub?output=csv{$tab}");
        }

        if (preg_match('#^/spreadsheets/d/([\w-]+)#', $path, $match) === 1) {
            return new self($url, "https://docs.google.com/spreadsheets/d/{$match[1]}/export?format=csv{$tab}");
        }

        return null;
    }
}
