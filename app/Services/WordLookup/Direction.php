<?php

declare(strict_types=1);

namespace App\Services\WordLookup;

/**
 * What a lookup turns a word into.
 *
 * Every direction puts English on the card's front. What goes on the back is
 * the Persian translation (en-fa), the Persian word that was typed (fa-en), or
 * the English meaning itself (en-en, a monolingual dictionary).
 */
enum Direction: string
{
    case EnglishToPersian = 'en-fa';
    case PersianToEnglish = 'fa-en';
    case EnglishToEnglish = 'en-en';

    public function source(): Language
    {
        return $this === self::PersianToEnglish ? Language::Persian : Language::English;
    }

    public function target(): Language
    {
        return $this === self::EnglishToPersian ? Language::Persian : Language::English;
    }

    /**
     * The direction for text whose language was not given: into the other
     * language, as before EN → EN existed.
     */
    public static function detect(string $text): self
    {
        return Language::detect($text) === Language::Persian ? self::PersianToEnglish : self::EnglishToPersian;
    }

    /**
     * The direction chosen by Google Translate-style parameters, or null to
     * detect it: /lookup?q=hello&sl=en&tl=en.
     *
     * sl=en&tl=en is the English dictionary. Otherwise one side implies the
     * other, so sl alone is enough and tl alone works too (tl=fa means from
     * English). sl=auto, or anything not understood, means detect.
     */
    public static function fromQuery(mixed $sl, mixed $tl = null): ?self
    {
        if ($sl === 'auto') {
            return null;
        }

        $source = is_string($sl) ? Language::tryFrom(strtolower($sl)) : null;
        $target = is_string($tl) ? Language::tryFrom(strtolower($tl)) : null;

        return match (true) {
            $source === Language::English && $target === Language::English => self::EnglishToEnglish,
            $source === Language::English => self::EnglishToPersian,
            $source === Language::Persian => self::PersianToEnglish,
            $target !== null => $target === Language::Persian ? self::EnglishToPersian : self::PersianToEnglish,
            default => null,
        };
    }
}
