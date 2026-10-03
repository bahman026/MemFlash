<?php

declare(strict_types=1);

namespace App\Services\WordLookup\Providers;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * The HTTP client every lookup provider uses. The class using it needs an
 * `int $timeout` property, in seconds.
 */
trait CallsLookupService
{
    protected function http(): PendingRequest
    {
        return Http::timeout($this->timeout)
            ->connectTimeout(min(3, $this->timeout))
            ->acceptJson()
            // Wikimedia rejects API requests that do not identify their client.
            ->withUserAgent(config('app.name') . '/1.0 (+' . config('app.url') . ')');
    }
}
