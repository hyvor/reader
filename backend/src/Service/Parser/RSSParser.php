<?php

namespace App\Service\Parser;

use App\Entity\Feed;
use App\Entity\Item;
use App\Service\Parser\Types\Author;
use App\Service\Parser\Types\FeedType;
use App\Service\Parser\Types\Tag;

class RSSParser implements ParserInterface
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
        $channel = $rssElement->getElementsByTagName('channel')->item(0);

        if (!$channel) {
            throw new ParserException('Invalid RSS feed. <channel> element not found');
        }

        $title = $channel->getElementsByTagName('title')->item(0)?->textContent ?? '';
        $homepageUrl = $channel->getElementsByTagName('link')->item(0)?->textContent ?? '';
        $description = $channel->getElementsByTagName('description')->item(0)?->textContent;
        $language = $channel->getElementsByTagName('language')->item(0)?->textContent;

        $itemsObjects = [];
        $items = $channel->getElementsByTagName('item');
        foreach ($items as $item) {
            try {
                $itemsObjects[] = $this->parseItem($item);
            } catch (ParserException) {
                continue;
            }
        }

        return new Feed(
            FeedType::RSS,
            $version,
            $title,
            $homepageUrl,
            null,
            $description,
            null,
            $language,
            $itemsObjects
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

        if (empty($url)) {
            throw new ParserException('Item must have a link');
        }

        $image = $this->getImage($item);

        $publishedAt = $item->getElementsByTagName('pubDate')->item(0)?->textContent;
        $publishedAt = $publishedAt ? \DateTimeImmutable::createFromFormat(DATE_RSS, $publishedAt) ?: null : null;

        $authors = [];
        $authorElements = $item->getElementsByTagName('author');
        foreach ($authorElements as $authorElement) {
            $authors[] = new Author(
                $authorElement->textContent,
                null,
                null
            );
        }

        $dcCreatorElements = $item->getElementsByTagNameNS('http://purl.org/dc/elements/1.1/', 'creator');
        foreach ($dcCreatorElements as $creatorElement) {
            $authors[] = new Author(
                $creatorElement->textContent,
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
        $mediaElements = $item->getElementsByTagNameNS('http://search.yahoo.com/mrss/', 'content');
        foreach ($mediaElements as $element) {
            $type = $element->getAttribute('type');
            if (str_starts_with($type, 'image/')) {
                return $element->getAttribute('url');
            }
        }

        $enclosures = $item->getElementsByTagName('enclosure');
        foreach ($enclosures as $element) {
            $type = $element->getAttribute('type');
            if (str_starts_with($type, 'image/')) {
                return $element->getAttribute('url');
            }
        }

        return null;
    }
}
