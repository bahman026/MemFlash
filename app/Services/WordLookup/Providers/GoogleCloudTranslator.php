<?php

declare(strict_types=1);

namespace App\Services\WordLookup\Providers;

use App\Services\WordLookup\Contracts\TranslationProvider;
use App\Services\WordLookup\Language;
use App\Services\WordLookup\Text;
use App\Services\WordLookup\Translation;

/**
 * Google Cloud Translation (Basic, v2). Needs an API key, GOOGLE_TRANSLATE_KEY;
 * without one it answers null and the next translator is asked, so it can sit
 * first in the list before a key exists.
 *
 * This is the official API, not the keyless translate.googleapis.com endpoint
 * the Google Translate page uses: that one is undocumented and answers bursts
 * of server traffic with a captcha page.
 *
 * @see https://cloud.google.com/translate/docs/reference/rest/v2/translate
 */
final class GoogleCloudTranslator implements TranslationProvider
{
    use CallsLookupService;

    private const URL = 'https://translation.googleapis.com/language/translate/v2';

    public function __construct(
        private readonly int $timeout = 5,
        private readonly ?string $key = null,
    ) {}

    public function translate(string $text, Language $from, Language $to): ?Translation
    {
        if ($this->key === null || $this->key === '') {
            return null;
        }

        // The key goes in a header rather than ?key=, so it stays out of any
        // URL that gets logged.
        $response = $this->http()
            ->withHeaders(['X-Goog-Api-Key' => $this->key])
            ->asForm()
            ->post(self::URL, ['q' => $text, 'source' => $from->value, 'target' => $to->value, 'format' => 'text']);

        if (! $response->successful()) {
            return null;
        }

        $texts = Text::translations([$response->json('data.translations.0.translatedText')], $to, 1);

        return $texts === [] ? null : new Translation('google', $texts);
    }
}
