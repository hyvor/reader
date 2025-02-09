<?php

namespace App\Domain\FeedItem;

use App\Domain\Feed\Feed\Feed;
use App\Domain\Feed\Feed\Item;
use App\Models\Feed as FeedModel;
use App\Models\FeedItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class FeedItemService
{

    public static function getItems(int $feedId)
    {
        return FeedItem::where('feed_id', $feedId)
            ->get();
    }

    /**
     * @return Collection<int, FeedItem>
     */
    public static function getItemsFromParsedFeed(FeedModel $feed, Feed $parsedFeed)
    {
        $ids = self::getIdsFromParsedFeed($parsedFeed);

        return FeedItem::where('feed_id', $feed->id)
            ->whereIn('guid', $ids)
            ->get();
    }

    public static function getIdsFromParsedFeed(Feed $parsedFeed)
    {
        /** @var string[] $ids */
        $ids = [];

        foreach ($parsedFeed->items as $item) {
            $ids[] = $item->id;
        }

        return $ids;
    }

    public static function createFromParsedItem(FeedModel $feed, Item $item) : FeedItem
    {
        $attributes = self::parsedItemToAttributes($feed, $item);
        return FeedItem::create($attributes);
    }

    public static function parsedItemToAttributes(FeedModel $feed, Item $item): array
    {
        return [
            'feed_id' => $feed->id,
            'guid' => $item->id,
            'url' => $item->url,
            'title' => $item->title,
            'published_at' => $item->published_at,
            'content_html' => $item->content_html,
            'content_text' => $item->content_text,
            'summary' => $item->summary,
            'image' => $item->image,
            'authors' => [], // $item->authors,
            'tags' => [], // $item->tags,
            'language' => $item->language,
        ];
    }

    public static function hasItemUpdated(Item $item, FeedItem $itemModel): bool
    {
        if ($item->title !== $itemModel->title) {
            return true;
        }

        if ($item->summary !== $itemModel->summary) {
            return true;
        }

        if ($item->url !== $itemModel->url) {
            return true;
        }

        if ($item->published_at->format('Y-m-d H:i:s') !== $itemModel->published_at->format('Y-m-d H:i:s')) {
            return true;
        }

        if ($item->content_html !== $itemModel->content_html) {
            return true;
        }

        if ($item->content_text !== $itemModel->content_text) {
            return true;
        }


        return false;
    }

}
