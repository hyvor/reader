<?php

namespace App\Domain\FeedSubscription\Event;

use App\Models\Feed;
use App\Models\FeedSubscription;
use App\Models\User;

class FeedSubscriptionCreatedEvent
{

    public function __construct(
        public FeedSubscription $subscription,
        public User $user,
        public Feed $feed
    ) {
    }

}
