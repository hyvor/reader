<?php

namespace App\Http\AppApi\Objects;

use App\Models\Feed;

class FeedObject
{

    public int $id;
    public int $created_at;
    public string $url;
    public ?string $title;
    public ?string $description;
    public int $subscribers;

    public function __construct(Feed $feed)
    {
        $this->id = $feed->id;
        $this->created_at = $feed->created_at->getTimestamp();
        $this->url = $feed->url;
        $this->title = $feed->title;
        $this->description = $feed->description;
        $this->subscribers = $feed->subscribers;
    }

}
