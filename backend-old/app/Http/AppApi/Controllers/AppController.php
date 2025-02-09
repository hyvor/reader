<?php

namespace App\Http\AppApi\Controllers;

use App\Domain\FeedSubscription\FeedSubscriptionService;
use App\Http\AppApi\Middleware\EnsureUser;
use App\Http\AppApi\Objects\FeedObject;
use Illuminate\Http\JsonResponse;

class AppController
{

    public function init(): JsonResponse
    {
        $user = EnsureUser::user();
        $feeds = FeedSubscriptionService::getFeedSubscriptions($user)
            ->map(fn ($subscription) => $subscription->feed)
            ->mapInto(FeedObject::class);

        return response()->json([
            'feeds' => $feeds,
        ]);
    }

}
