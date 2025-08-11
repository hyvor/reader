<?php

namespace App\Service\Parser;

use App\Service\Parser\Types\Feed;
use App\Service\Parser\Types\Item;
use App\Service\Parser\Types\Author;
use App\Service\Parser\Types\Tag;
use App\Service\Parser\Types\FeedType;

class JsonFeedParser implements ParserInterface
{
    /**
     * @var array<mixed, mixed>
     */
    public array $json;

    public function __construct(string $content)
    {
        if (empty($content)) {
            throw new ParserException('Empty content');
        }

        $json = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ParserException('Invalid JSON');
        }

        if (!is_array($json)) {
            throw new ParserException('JSON must be an object');
        }

        $this->json = $json;
    }

    public function parse(): Feed
    {
        $version = $this->json['version'] ?? '';
        if (empty($version)) {
            throw new ParserException('Required field missing: version');
        }

        $title = $this->safeStrval($this->json['title'] ?? '');
        if (empty($title)) {
            throw new ParserException('Required field missing: title');
        }

        $homepageUrl = $this->safeStrval($this->json['home_page_url'] ?? '');
        $feed = new Feed(
            type: FeedType::JSONFEED, 
            version: $this->safeStrval($version), 
            title: $title, 
            homepage_url: $homepageUrl, 
            feed_url: $this->safeStrval($this->json['feed_url'] ?? null), 
            description: $this->safeStrval($this->json['description'] ?? null),
            icon: $this->safeStrval($this->json['favicon'] ?? $this->json['icon'] ?? null),
            language: $this->safeStrval($this->json['language'] ?? null)
        );

        $items = $this->json['items'] ?? [];
        $items = is_array($items) ? $items : [];
        $itemsObjects = [];
        foreach ($items as $item) {
            try {
                $itemsObjects[] = $this->parse_item($item);
            } catch (ParserException) {
                continue;
            }
        }

        $feed->items = $itemsObjects;

        return $feed;
    }

    /**
     * @param mixed $item
     * @return Item
     */
    private function parse_item(mixed $item): Item
    {
        if (!is_array($item)) {
            throw new ParserException('Item must be an array');
        }

        $id = $this->safeStrval($item['id'] ?? '');
        $url = $this->safeStrval($item['url'] ?? '');
        $title = $this->safeStrval($item['title'] ?? '');

        $contentHtml = $item['content_html'] ?? null;
        $contentHtml = empty($contentHtml) ? null : $this->safeStrval($contentHtml);

        $contentText = $item['content_text'] ?? null;
        $contentText = empty($contentText) ? null : $this->safeStrval($contentText);

        if ($contentHtml === null && $contentText === null) {
            throw new ParserException('Either content_html or content_text must be provided');
        }

        $summary = $item['summary'] ?? null;
        $summary = empty($summary) ? null : $this->safeStrval($summary);

        $imageValue = $item['image'] ?? null;
        $bannerImageValue = $item['banner_image'] ?? null;
        $image = $imageValue ?? $bannerImageValue ?? null;
        $image = empty($image) ? null : $this->safeStrval($image);

        $publishedAt = $this->date($item['date_published'] ?? null);
        $updatedAt = $this->date($item['date_modified'] ?? null);

        $authorsValue = $item['authors'] ?? [];
        $authorsValue = is_array($authorsValue) ? $authorsValue : [];
        $authors = [];

        foreach ($authorsValue as $author) {
            try {
                $authors[] = $this->parse_author($author);
            } catch (ParserException) {
                continue;
            }
        }

        $tagsValue = $item['tags'] ?? [];
        $tagsValue = is_array($tagsValue) ? $tagsValue : [];
        $tags = [];

        foreach ($tagsValue as $tag) {
            $tags[] = new Tag($this->safeStrval($tag));
        }

        $language = $item['language'] ?? null;
        $language = empty($language) ? null : $this->safeStrval($language);

        return new Item(
            $id,
            $url,
            $title,
            $contentHtml,
            $contentText,
            $summary,
            $image,
            $publishedAt,
            $updatedAt,
            $authors,
            $tags,
            $language
        );
    }

    /**
     * @param mixed $author
     * @return Author
     */
    private function parse_author(mixed $author): Author
    {
        if (!is_array($author)) {
            throw new ParserException('Author must be an array');
        }

        $name = $author['name'] ?? null;

        if (!is_string($name)) {
            throw new ParserException('Author name is required');
        }

        $url = $author['url'] ?? null;
        $url = empty($url) ? null : $this->safeStrval($url);

        $avatar = $author['avatar'] ?? null;
        $avatar = empty($avatar) ? null : $this->safeStrval($avatar);

        return new Author($name, $url, $avatar);
    }

    private function date(mixed $date): ?\DateTimeImmutable
    {
        if (!is_string($date)) {
            return null;
        }

        try {
            return new \DateTimeImmutable($date);
        } catch (\Exception) {
            return null;
        }
    }

    private function safeStrval(mixed $value): string
    {
        if (is_scalar($value) || $value === null) {
            return strval($value);
        }
        return '';
    }
}
