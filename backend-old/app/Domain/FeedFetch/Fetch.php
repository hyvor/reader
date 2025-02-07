<?php

namespace App\Domain\FeedFetch;

use App\Domain\Feed\Exception\FeedFetchException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class Fetch
{

    /**
     * @throws FeedFetchException
     */
    public static function fetch(string $url): Response
    {
        try {
            $response = Http::get($url);

            // if the code is 4xx or 5xx
            if (!self::isSuccessful($response)) {
                throw new FeedFetchException('Failed to fetch the feed with status: ' . $response->status());
            }

            return $response;
        } catch (ConnectionException $e) {
            throw new FeedFetchException('Failed to fetch the feed. ' . $e->getMessage());
        }
    }

    private static function isSuccessful(Response $response): bool
    {
        // success
        if ($response->successful()) {
            return true;
        }
        // not modified
        if ($response->status() === 304) {
            return true;
        }

        return false;
    }

}
