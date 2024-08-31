<?php

namespace App\Domain\FeedFetch;

use App\Domain\Feed\Exception\FeedFetchException;
use App\Domain\Feed\Exception\ParserException;
use App\Domain\FeedItem\FeedItemService;
use App\Domain\Feed\Feed\Feed;
use App\Domain\Feed\Feed\Item;
use App\Domain\Feed\Parser;
use App\Models\Feed as FeedModel;
use App\Models\FeedFetch;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;

class FetchJob implements ShouldQueue, ShouldBeUnique
{

    public FeedFetch $fetch;

    public function uniqueId(): string
    {
        return (string)$this->feed->id;
    }

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
            $startTimeMicro = microtime(true);
            $response = Fetch::fetch($this->feed->url);
            $endTimeMicro = microtime(true);
            $latencyMs = (int)(($endTimeMicro - $startTimeMicro) * 1000);

            $newItems = [];
            $updatedItems = [];

            $isConditionalRequest = $response->status() === 304;

            if (!$isConditionalRequest) {
                $parsedFeed = Parser::fromResponse($response);
                ['new' => $newItems, 'updated' => $updatedItems] = $this->getNewAndUpdatedItems($parsedFeed);
            }

            $this->fetch->update([
                'status' => FetchStatusEnum::COMPLETED,
                'status_code' => $response->status(),
                'new_items_count' => count($newItems),
                'updated_items_count' => count($updatedItems),
                'latency_ms' => $latencyMs,
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

    /**
     * @return array{new: Item[], updated: Item[]}
     */
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
