<?php

declare(strict_types=1);

namespace App\Services\WordLookup;

use App\Services\WordLookup\Contracts\DefinitionProvider;
use App\Services\WordLookup\Contracts\TranslationProvider;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

/**
 * Looks a word up: English meanings and a Persian translation for English
 * input, an English equivalent for Persian input.
 *
 * Which services answer is configuration (services.word_lookup), not code.
 * Each role has an ordered list of providers and the first one to answer
 * wins, so a source can be swapped, added or put behind another as a fallback
 * from .env alone.
 */
final class WordLookupService
{
    /**
     * Meanings shown per word. Dictionaries list dozens for common words, and
     * the rare ones are not what a learner looking a word up is after.
     */
    public const MAX_SENSES = 4;

    /**
     * A result with a part missing is kept briefly, then asked for again: the
     * gap may be an outage or a spent daily quota rather than an unknown word.
     */
    private const INCOMPLETE_CACHE_MINUTES = 10;

    private readonly string $cachePrefix;

    /**
     * @param  list<DefinitionProvider>  $definitions  asked in order
     * @param  list<TranslationProvider>  $translators  asked in order
     */
    public function __construct(
        private readonly array $definitions,
        private readonly array $translators,
        private readonly CacheRepository $cache,
        private readonly int $cacheDays = 30,
    ) {
        // Part of every key, so changing the providers takes effect at once
        // instead of when the old answers expire. The version is bumped when
        // LookupResult::toArray() changes shape (v2: directions, for en-en).
        $this->cachePrefix = 'word-lookup:v2:' . substr(sha1(implode(',', array_map(
            fn (object $provider): string => $provider::class,
            [...$definitions, ...$translators],
        ))), 0, 8) . ':';
    }

    /**
     * Builds the service from services.word_lookup.
     *
     * An unknown provider name throws instead of being skipped: a typo in .env
     * should be an error in the log, not a lookup that quietly lost a source.
     *
     * @param  array<string, mixed>  $config
     */
    public static function fromConfig(array $config, Container $container, CacheRepository $cache): self
    {
        $make = function (string $role, string $contract) use ($config, $container): array {
            return array_map(function (string $name) use ($config, $container, $role, $contract): object {
                $options = $config['providers'][$name]
                    ?? throw new InvalidArgumentException("Unknown word lookup provider [{$name}] in services.word_lookup.{$role}.");

                // Options other than the class are constructor arguments by name,
                // e.g. a provider's API key.
                $arguments = ['timeout' => (int) $config['timeout']] + array_diff_key($options, ['class' => true]);
                $provider = $container->make($options['class'], $arguments);

                if (! $provider instanceof $contract) {
                    throw new InvalidArgumentException("Word lookup provider [{$name}] does not implement {$contract}.");
                }

                return $provider;
            }, array_values($config[$role]));
        };

        return new self(
            definitions: $make('definitions', DefinitionProvider::class),
            translators: $make('translators', TranslationProvider::class),
            cache: $cache,
            cacheDays: (int) $config['cache_days'],
        );
    }

    /**
     * @param  Direction|null  $direction  null detects it from the script: Persian
     *                                     text goes to English, English to Persian
     */
    public function lookup(string $input, ?Direction $direction = null): LookupResult
    {
        $query = Text::clean($input);
        $direction ??= Direction::detect($query);

        if ($query === '') {
            return new LookupResult($query, $direction);
        }

        $key = $this->cachePrefix . $direction->value . ':' . sha1($query);
        $cached = $this->cache->get($key);

        if (is_array($cached)) {
            return LookupResult::fromArray($cached);
        }

        $result = match ($direction) {
            Direction::EnglishToPersian, Direction::EnglishToEnglish => $this->lookUpEnglish($query, $direction),
            Direction::PersianToEnglish => $this->lookUpPersian($query),
        };

        $this->cache->put(
            $key,
            $result->toArray(),
            $result->isComplete() ? now()->addDays($this->cacheDays) : now()->addMinutes(self::INCOMPLETE_CACHE_MINUTES),
        );

        return $result;
    }

    /**
     * English in: its meanings, and for en-fa a Persian translation too. en-en
     * is a dictionary lookup, so no translator is asked at all.
     */
    private function lookUpEnglish(string $word, Direction $direction): LookupResult
    {
        /** @var Definition|null $definition */
        $definition = $this->firstAnswer($this->definitions, fn (DefinitionProvider $provider) => $provider->define($word));

        /** @var Translation|null $translation */
        $translation = $direction === Direction::EnglishToPersian
            ? $this->firstAnswer(
                $this->translators,
                fn (TranslationProvider $provider) => $provider->translate($word, Language::English, Language::Persian),
            )
            : null;

        return new LookupResult(
            query: $word,
            direction: $direction,
            senses: array_slice($definition->senses ?? [], 0, self::MAX_SENSES),
            translations: $translation->texts ?? [],
            phonetic: $definition?->phonetic,
            sources: array_values(array_filter([$definition?->source, $translation?->source])),
        );
    }

    /**
     * Persian in, English out. A word is enough here, so no definition: the
     * learner already knows what the Persian means.
     */
    private function lookUpPersian(string $word): LookupResult
    {
        /** @var Translation|null $translation */
        $translation = $this->firstAnswer(
            $this->translators,
            fn (TranslationProvider $provider) => $provider->translate($word, Language::Persian, Language::English),
        );

        return new LookupResult(
            query: $word,
            direction: Direction::PersianToEnglish,
            translations: $translation->texts ?? [],
            sources: $translation ? [$translation->source] : [],
        );
    }

    /**
     * The first non-null answer, asking providers in configured order.
     *
     * A provider that throws -- a timeout, a changed response format -- is
     * logged and skipped. One broken source must not take the lookup down.
     *
     * @param  list<object>  $providers
     */
    private function firstAnswer(array $providers, callable $ask): ?object
    {
        foreach ($providers as $provider) {
            try {
                $answer = $ask($provider);
            } catch (Throwable $e) {
                Log::warning('Word lookup provider failed', [
                    'provider' => $provider::class,
                    'error' => $e->getMessage(),
                ]);

                continue;
            }

            if ($answer !== null) {
                return $answer;
            }
        }

        return null;
    }
}
