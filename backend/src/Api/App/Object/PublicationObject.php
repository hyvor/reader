<?php

namespace App\Api\App\Object;

use App\Entity\Publication;

class PublicationObject
{
    public int $id;
    public string $uuid;
    public string $title;
    public string $url;
    public string $description;
    public int $subscribers;
    public int $created_at;
    public int $updated_at;

    public function __construct(Publication $publication)
    {
        $this->id = $publication->getId();
        $this->uuid = $publication->getUuid()->toRfc4122();
        $this->title = $publication->getTitle() ?? 'Untitled';
        $this->url = $publication->getUrl();
        $this->description = $publication->getDescription() ?? '';
        $this->subscribers = $publication->getSubscribers();
        $this->created_at = $publication->getCreatedAt()->getTimestamp();
        $this->updated_at = $publication->getUpdatedAt()->getTimestamp();
    }
} 