<?php

namespace App\Service\Parser;

use App\Service\Parser\Types\Feed;
use App\Service\Parser\Types\Item;
use App\Service\Parser\Types\Author;
use App\Service\Parser\Types\Tag;
use App\Service\Parser\Types\FeedType;

class AtomParser implements ParserInterface
{
    private \DOMDocument $document;

    public function __construct(string $content)
    {
        if (empty($content)) {
            throw new ParserException('Empty content');
        }

        $this->document = new \DOMDocument();
        if (!@$this->document->loadXML($content, LIBXML_NOERROR | LIBXML_NOWARNING)) {
            throw new ParserException('Invalid XML');
        }
    }

    public function parse(): Feed
    {
        $feedElement = $this->document->documentElement;

        if (!$feedElement || $feedElement->tagName !== 'feed') {
            throw new ParserException('Invalid Atom feed. <feed> element not found');
        }

        $title = $this->get_text_content($feedElement, 'title');
        if (empty($title)) {
            throw new ParserException('Required field missing: title');
        }

        $id = $this->get_text_content($feedElement, 'id');
        if (empty($id)) {
            throw new ParserException('Required field missing: id');
        }

        $updated = $this->get_text_content($feedElement, 'updated');
        if (empty($updated)) {
            throw new ParserException('Required field missing: updated');
        }

        $homepageUrl = $this->get_alternate_link($feedElement);
        if (empty($homepageUrl)) {
            throw new ParserException('Required field missing: link');
        }

        $feed = new Feed(
            type: FeedType::ATOM, 
            version: '1.0', 
            title: $title, 
            homepage_url: $homepageUrl, 
            feed_url: $this->get_self_link($feedElement), 
            description: $this->get_text_content($feedElement, 'subtitle'),
            icon: $this->get_text_content($feedElement, 'icon'),
            language: $feedElement->getAttribute('xml:lang') ?: null,
            updated_at: $this->get_date($feedElement, 'updated'),
            generator: $this->get_text_content($feedElement, 'generator')
        );

        $itemsObjects = [];
        $entries = $feedElement->getElementsByTagName('entry');
        foreach ($entries as $entry) {
            try {
                $itemsObjects[] = $this->parse_entry($entry);
            } catch (ParserException) {
                continue;
            }
        }

        $feed->items = $itemsObjects;

        return $feed;
    }

    private function parse_entry(\DOMElement $entry): Item
    {
        $id = $this->get_text_content($entry, 'id');
        if (empty($id)) {
            throw new ParserException('Entry must have an id');
        }

        $url = $this->get_alternate_link($entry);
        if (empty($url)) {
            throw new ParserException('Entry must have an alternate link');
        }

        $title = $this->get_text_content($entry, 'title');
        $summary = $this->get_text_content($entry, 'summary');
        $content = $this->get_content($entry);
        $language = $entry->getAttribute('xml:lang');

        if (empty($language)) {
            $language = null;
        }

        $publishedAt = $this->get_date($entry, 'published');
        if ($publishedAt === null) {
            $publishedAt = $this->get_date($entry, 'issued');
        }

        $updatedAt = $this->get_date($entry, 'updated');
        if ($updatedAt === null) {
            $updatedAt = $this->get_date($entry, 'modified');
        }

        $authors = [];
        $authorElements = $entry->getElementsByTagName('author');
        foreach ($authorElements as $authorElement) {
            try {
                $authors[] = $this->parse_author($authorElement);
            } catch (ParserException) {
                continue;
            }
        }

        $tags = [];
        $categoryElements = $entry->getElementsByTagName('category');
        foreach ($categoryElements as $categoryElement) {
            $term = $categoryElement->getAttribute('term');
            if (!empty($term)) {
                $tags[] = new Tag($term);
            }
        }

        return new Item(
            $id,
            $url,
            $title,
            $content,
            null,
            $summary,
            null,
            $publishedAt,
            $updatedAt,
            $authors,
            $tags,
            $language
        );
    }

    private function parse_author(\DOMElement $author): Author
    {
        $name = $this->get_text_content($author, 'name');
        if (empty($name)) {
            throw new ParserException('Author must have a name');
        }

        $url = $this->get_text_content($author, 'uri');
        if (empty($url)) {
            $url = null;
        }

        return new Author($name, $url, null);
    }

    private function get_text_content(\DOMElement $element, string $tagName): string
    {
        $node = $element->getElementsByTagName($tagName)->item(0);
        return $node?->textContent ?? '';
    }

    private function get_content(\DOMElement $entry): ?string
    {
        $content = $entry->getElementsByTagName('content')->item(0);
        if (!$content) {
            return null;
        }

        $type = $content->getAttribute('type');
        if ($type === 'html' || $type === 'xhtml') {
            return $content->textContent;
        }

        return null;
    }

    private function get_date(\DOMElement $element, string $tagName): ?\DateTimeImmutable
    {
        $date = $this->get_text_content($element, $tagName);
        if (empty($date)) {
            return null;
        }

        try {
            return new \DateTimeImmutable($date);
        } catch (\Exception) {
            return null;
        }
    }

    private function get_alternate_link(\DOMElement $element): string
    {
        $links = $element->getElementsByTagName('link');
        foreach ($links as $link) {
            $rel = $link->getAttribute('rel');
            if (empty($rel) || $rel === 'alternate') {
                return $link->getAttribute('href') ?? '';
            }
        }
        return '';
    }

    private function get_self_link(\DOMElement $element): ?string
    {
        $links = $element->getElementsByTagName('link');
        foreach ($links as $link) {
            $rel = $link->getAttribute('rel');
            if ($rel === 'self') {
                $href = $link->getAttribute('href');
                return empty($href) ? null : $href;
            }
        }
        return null;
    }
}
