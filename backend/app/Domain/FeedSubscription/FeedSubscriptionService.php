<?php

namespace App\Domain\FeedSubscription;

use App\Domain\FeedSubscription\Event\FeedSubscriptionCreatedEvent;
use App\Models\Feed;
use App\Models\FeedSubscription;
use App\Models\User;

class FeedSubscriptionService
{

    public static function createSubscription(User $user, Feed $feed): FeedSubscription
    {
        $subscription = FeedSubscription::create([
            'user_id' => $user->id,
            'feed_id' => $feed->id,
        ]);

        event(new FeedSubscriptionCreatedEvent($subscription, $user, $feed));

        return $subscription;
    }

}
