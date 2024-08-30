<?php

namespace app\Domain\FeedFetch;

use App\Models\Feed;

class FetchDispatchJob
{

    public function handle()
    {
        $feeds = Feed::where('next_fetch_at', '<=', now())->get();

        foreach ($feeds as $feed) {
            dispatch(new FetchJob($feed));
        }
    }

}
