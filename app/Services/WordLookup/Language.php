<?php

declare(strict_types=1);

namespace App\Services\WordLookup;

/**
 * The two languages the lookup understands. Values are the ISO 639-1 codes the
 * translation services expect.
 */
enum Language: string
{
    case English = 'en';
    case Persian = 'fa';

    /**
     * Persian if the text contains any Arabic-script letter, English otherwise.
     *
     * Script is decisive here and needs no service: Persian is written in Arabic
     * script and English never is, so "پیروز شدن" and "win" can't be confused.
     */
    public static function detect(string $text): self
    {
        return preg_match('/\p{Arabic}/u', $text) === 1 ? self::Persian : self::English;
    }
}
