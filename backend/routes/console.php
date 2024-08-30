<?php

use App\Domain\FeedFetch\FetchDispatchJob;
use Illuminate\Support\Facades\Artisan;

// DEV

Artisan::command('app:feed:dispatch', fn() => (new FetchDispatchJob())->handle())->describe(
    'Dispatches the feed fetch job'
);
