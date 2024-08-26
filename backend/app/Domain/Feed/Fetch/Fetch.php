<?php

namespace app\Domain\Feed\Fetch;

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

            if ($response->failed()) {
                throw new FeedFetchException('Failed to fetch the feed with status: ' . $response->status());
            }

            return $response;
        } catch (ConnectionException $e) {
            throw new FeedFetchException('Failed to fetch the feed. ' . $e->getMessage());
        }
    }

}
