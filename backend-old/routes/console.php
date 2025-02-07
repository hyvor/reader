<?php

use App\Domain\FeedFetch\FetchDispatchJob;
use App\Domain\FeedFetch\FetchJob;
use App\Models\Feed;
use Illuminate\Support\Facades\Artisan;

// DEV

Artisan::command('app:feed:dispatch', fn() => (new FetchDispatchJob())->handle())->describe(
    'Dispatches the feed fetch job'
);

Artisan::command('test:feed:fetch', function () {
    $feed = Feed::first();
    $job = new FetchJob($feed);
    $job->handle();
});
