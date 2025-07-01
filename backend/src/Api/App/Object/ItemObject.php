<?php

namespace App\Api\App\Object;

use App\Entity\Item;

class ItemObject
{
    public int $id;
    public string $guid;
    public string $uuid;
    public string $title;
    public string $url;
    public ?string $content_html;
    public ?string $content_text;
    public ?string $summary;
    public ?string $image;
    public ?int $published_at;
    public ?int $updated_at;
    public array $authors;
    public array $tags;
    public ?string $language;
    public ?int $publication_id;
    public ?string $publication_uuid;
    public ?string $publication_title;

    public function __construct(Item $item)
    {
        $this->id = $item->getId();
        $this->guid = $item->getGuid();
        $this->uuid = $item->getUuid();
        $this->title = $item->getTitle() ?? 'Untitled';
        $this->url = $item->getUrl();
        $this->content_html = $item->getContentHtml();
        $this->content_text = $item->getContentText();
        $this->summary = $item->getSummary();
        $this->image = $item->getImage();
        $this->published_at = $item->getPublishedAt()?->getTimestamp();
        $this->updated_at = $item->getUpdatedAt()?->getTimestamp();
        $this->authors = $item->getAuthors();
        $this->tags = $item->getTags();
        $this->language = $item->getLanguage();
        $this->publication_id = $item->getPublication()?->getId();
        $this->publication_uuid = $item->getPublication()?->getUuid();
        $this->publication_title = $item->getPublication()?->getTitle() ?? 'Untitled';
    }
} 