<?php

namespace App\Domain\Feed\Fetch;

use App\Domain\Feed\Exception\FeedFetchException;
use App\Domain\FeedItem\FeedItemService;
use App\Domain\FeedParser\Feed\Feed;
use App\Domain\FeedParser\Parser\ParserException;
use App\Models\Feed as FeedModel;
use App\Models\FeedFetch;
use Hyvor\FeedParser\Parser;

class FetchJob
{

    public FeedFetch $fetch;

    public function __construct(
        public FeedModel $feed
    ) {
        $this->fetch = FeedFetch::create([
            'feed_id' => $this->feed->id,
            'status' => FetchStatusEnum::PENDING,
        ]);
    }

    public function handle(): void
    {
        try {
            $response = Fetch::fetch($this->feed->url);
            $parsedFeed = Parser::fromResponse($response);

            ['new' => $newItems, 'updated' => $updatedItems] = $this->getNewAndUpdatedItems($parsedFeed);

            $this->fetch->update([
                'status' => FetchStatusEnum::COMPLETED,
                'status_code' => $response->status(),
                'new_items_count' => 0,
                'updated_items_count' => 0,
            ]);
        } catch (\Exception $exception) {
            $error = 'Internal server error';

            if ($exception instanceof FeedFetchException || $exception instanceof ParserException) {
                $error = $exception->getMessage();
            }

            $this->fetch->update([
                'status' => FetchStatusEnum::FAILED,
                'status_code' => isset($response) ? $response->status() : 0,
                'error' => $error,
                'error_private' => (string)mb_convert_encoding($exception, 'UTF-8'),
            ]);
        }
    }

    private function getNewAndUpdatedItems(Feed $parsedFeed)
    {
        $items = FeedItemService::getItemsFromParsedFeed($this->feed, $parsedFeed);

        $newItems = [];
        $updatedItems = [];

        foreach ($parsedFeed->items as $parsedItem) {
            $item = $items->firstWhere('guid', $parsedItem->id);

            if ($item === null) {
                $newItems[] = $parsedItem;
            } else {
                $updatedItems[] = $parsedItem;
            }
        }

        return [
            'new' => $newItems,
            'updated' => $updatedItems,
        ];
    }

}
