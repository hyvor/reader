<?php

namespace App\Domain\Feed;

use App\Domain\Feed\Feed\Feed;
use App\Models\Feed as FeedModel;

class FeedService
{

    public static function byId(int $id): ?FeedModel
    {
        return FeedModel::find($id);
    }

    public static function byUrl(string $url): ?FeedModel
    {
        return FeedModel::where('url', $url)->first();
    }

    public static function createFeed(string $url, Feed $feed): FeedModel
    {
        $model = FeedModel::create([
            'url' => $url,
            'title' => $feed->title,
            'description' => $feed->description,
        ]);

        return $model->refresh();
    }

}
