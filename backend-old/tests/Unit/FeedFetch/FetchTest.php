<?php

namespace Unit\FeedFetch;

use App\Domain\Feed\Exception\FeedFetchException;
use App\Domain\FeedFetch\Fetch;
use Illuminate\Support\Facades\Http;

it('follows redirects', function () {
    Http::fake([
        'http://example.com/feed' => Http::response(null, 301, ['Location' => 'http://example.com/feed.xml']),
        'http://example.com/feed.xml' => Http::response('Feed content')
    ]);

    $response = Fetch::fetch('http://example.com/feed');

    expect($response->body())->toBe('Feed content');
});

it('returns on 304', function () {
    Http::fake([
        'http://example.com/feed' => Http::response(null, 304),
    ]);

    $response = Fetch::fetch('http://example.com/feed');

    expect($response->status())->toBe(304);
});

it('throws an error on other codes', function ($code) {
    Http::fake([
        'http://example.com/feed' => Http::response(null, $code),
    ]);

    expect(fn() => Fetch::fetch('http://example.com/feed'))->toThrow(
        FeedFetchException::class,
        'Failed to fetch the feed with status: ' . $code
    );
})->with([
    303,
    404,
    500
]);

it('throws an error on connection exception', function () {
    Http::fake([
        'http://example.com/feed' => function () {
            throw new \Illuminate\Http\Client\ConnectionException('Connection error');
        }
    ]);

    expect(fn() => Fetch::fetch('http://example.com/feed'))->toThrow(
        FeedFetchException::class,
        'Failed to fetch the feed. Connection error'
    );
});
