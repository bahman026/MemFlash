<?php

declare(strict_types=1);

use App\Services\WordLookup\Providers\DatamuseDefinitions;
use App\Services\WordLookup\Providers\GoogleCloudTranslator;
use App\Services\WordLookup\Providers\MyMemoryTranslator;
use App\Services\WordLookup\Providers\WiktionaryDefinitions;

$providerList = fn (string $value): array => array_values(array_filter(array_map('trim', explode(',', $value))));

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT'),
    ],

    /*
    | The dashboard's word lookup (App\Services\WordLookup).
    |
    | definitions / translators are provider names, asked in order until one
    | answers; an empty list turns that part of the lookup off. Add a provider
    | by implementing DefinitionProvider or TranslationProvider and registering
    | it under `providers`; any option besides `class` is passed to its
    | constructor by name.
    */
    'word_lookup' => [
        'definitions' => $providerList((string) env('WORD_LOOKUP_DEFINITIONS', 'wiktionary,datamuse')),
        // google answers nothing until GOOGLE_TRANSLATE_KEY is set, so listing
        // it first costs nothing and adding the key is all it takes.
        'translators' => $providerList((string) env('WORD_LOOKUP_TRANSLATORS', 'google,mymemory')),
        'timeout' => (int) env('WORD_LOOKUP_TIMEOUT', 5),
        'cache_days' => (int) env('WORD_LOOKUP_CACHE_DAYS', 30),

        'providers' => [
            'wiktionary' => ['class' => WiktionaryDefinitions::class],
            'datamuse' => ['class' => DatamuseDefinitions::class],
            'mymemory' => ['class' => MyMemoryTranslator::class, 'email' => env('MYMEMORY_EMAIL')],
            'google' => ['class' => GoogleCloudTranslator::class, 'key' => env('GOOGLE_TRANSLATE_KEY')],
        ],
    ],

];
