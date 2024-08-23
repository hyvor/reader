<?php

namespace Hyvor\FeedParser\Parser;

use App\Domain\FeedParser\Feed\Feed;
use App\Domain\FeedParser\Feed\Item;
use Hyvor\FeedParser\FeedType;

class JsonParser implements ParserInterface
{

    /**
     * @var array<mixed>
     */
    public array $json;

    public function __construct(string $content)
    {
        $json = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ParserException('Invalid JSON');
        }

        $this->json = $json;
    }

    public function parse(): Feed
    {
        $version = $this->json['version'] ?? '1.0';
        $version = str_replace('https://jsonfeed.org/version/', '', $version);

        $title = strval($this->json['title'] ?? '');
        $homepageUrl = strval($this->json['home_page_url'] ?? '');
        $feedUrl = strval($this->json['feed_url'] ?? '');

        $description = $this->json['description'] ?? '';
        $description = empty($description) ? null : strval($description);

        $icon = $this->json['icon'] ?? '';
        $favicon = $this->json['favicon'] ?? '';
        $icon = empty($favicon) ? $icon : $favicon;
        $icon = empty($icon) ? null : strval($icon);

        $language = $this->json['language'] ?? '';
        $language = empty($language) ? null : strval($language);

        $items = $this->json['items'] ?? [];
        $items = is_array($items) ? $items : [];
        $itemsObjects = [];
        foreach ($items as $item) {
            $itemsObjects[] = $this->parseItem($item);
        }

        return new Feed(
            FeedType::JSON,
            $version,
            $title,
            $homepageUrl,
            $feedUrl,
            $description,
            $icon,
            $language,
            items: $itemsObjects
        );
    }


    /**
     * @param array<mixed> $item
     * @return Item
     */
    private function parseItem(array $item): Item
    {
        //
    }
}
