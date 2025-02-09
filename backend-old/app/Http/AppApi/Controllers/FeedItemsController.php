<?php

namespace App\Http\AppApi\Controllers;

use App\Domain\Feed\FeedService;
use App\Domain\FeedItem\FeedItemService;
use App\Http\AppApi\Middleware\EnsureUser;
use App\Http\AppApi\Objects\FeedObject;
use Hyvor\Internal\Http\Exceptions\HttpException;
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

        $feed = FeedService::byId($feedId);
        if ($feed === null) {
            throw new HttpException('Feed not found');
        }

        $items = FeedItemService::getItems($feedId);

        return response()->json([
            'items' => $items,
            'feed' => new FeedObject($feed)
        ]);

    }

}
