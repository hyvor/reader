<?php

namespace App\Http\AppApi\Controllers;

use App\Domain\FeedItem\FeedItemService;
use App\Http\AppApi\Middleware\EnsureUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedItemsController
{

    public function getItems(Request $request) : JsonResponse
    {

        $user = EnsureUser::user();

        $request->validate([
            'type' => 'in:all,saved,feed',
            'feed_id' => 'integer',
        ]);

        /** @var ?int $feedId */
        $feedId = $request->input('feed_id');

        $items = FeedItemService::getItems($feedId);

        return response()->json($items);

    }

}
