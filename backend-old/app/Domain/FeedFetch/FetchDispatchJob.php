<?php

namespace App\Domain\FeedFetch;

use App\Models\Feed;
use Illuminate\Support\Facades\DB;

class FetchDispatchJob
{

    public function handle(): void
    {
        $feeds = Feed::where('next_fetch_at', '<=', now())->get();

        foreach ($feeds as $feed) {
            dispatch(new FetchJob($feed));
        }
    }

}
