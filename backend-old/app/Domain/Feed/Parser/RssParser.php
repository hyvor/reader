<?php

namespace App\Domain\Feed\Parser;

use App\Domain\Feed\Exception\ParserException;
use App\Domain\Feed\Feed\Author;
use App\Domain\Feed\Feed\Feed;
use App\Domain\Feed\Feed\Item;
use App\Domain\Feed\Feed\Tag;
use App\Domain\Feed\FeedType;
use Carbon\Carbon;

class RssParser implements ParserInterface
{

    private \DOMDocument $document;

    public function __construct(string $content)
    {
        $this->document = new \DOMDocument();
        $this->document->loadXML($content, LIBXML_NOERROR | LIBXML_NOWARNING);
    }

    public function parse(): Feed
    {
        $rssElement = $this->document->documentElement;

        if (!$rssElement || $rssElement->tagName !== 'rss') {
            throw new ParserException('Invalid RSS feed. <rss> element not found');
        }

        $version = $rssElement->getAttribute('version');

        $title = $rssElement->getElementsByTagName('title')->item(0)?->textContent ?? '';
        $homepageUrl = $rssElement->getElementsByTagName('link')->item(0)?->textContent ?? '';
        $description = $rssElement->getElementsByTagName('description')->item(0)?->textContent;
        $language = $rssElement->getElementsByTagName('language')->item(0)?->textContent;

        $itemsObjects = [];
        $items = $this->document->getElementsByTagName('item');
        foreach ($items as $item) {
            $itemsObjects[] = $this->parseItem($item);
        }

        return new Feed(
            FeedType::JSON,
            $version,
            $title,
            $homepageUrl,
            null,
            $description,
            null,
            $language,
            items: $itemsObjects
        );
    }

    private function parseItem(\DOMElement $item): Item
    {
        $id = $item->getElementsByTagName('guid')->item(0)?->textContent;
        $url = $item->getElementsByTagName('link')->item(0)?->textContent ?? '';
        $title = $item->getElementsByTagName('title')->item(0)?->textContent ?? '';
        $summary = $item->getElementsByTagName('description')->item(0)?->textContent;

        if ($id === null) {
            $id = $url;
        }

        $image = $this->getImage($item);

        $publishedAt = $item->getElementsByTagName('pubDate')->item(0)?->textContent;
        $publishedAt = $publishedAt ? Carbon::parse($publishedAt) : null;

        $authors = [];
        $authorElements = $item->getElementsByTagName('author');
        foreach ($authorElements as $authorElement) {
            $authors[] = new Author(
                $authorElement->textContent,
                null,
                null
            );
        }

        $tags = [];
        $categoryElements = $item->getElementsByTagName('category');
        foreach ($categoryElements as $categoryElement) {
            $tags[] = new Tag($categoryElement->textContent);
        }

        return new Item(
            $id,
            $url,
            $title,
            null,
            null,
            $summary,
            $image,
            $publishedAt,
            null,
            $authors,
            $tags,
            null
        );
    }

    private function getImage(\DOMElement $item): ?string
    {
        $enclosure = $item->getElementsByTagName('enclosure');

        foreach ($enclosure as $element) {
            if ($element->getAttribute('type') === 'image/*') {
                return $element->getAttribute('url');
            }
        }

        return null;
    }
}
