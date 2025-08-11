<?php

namespace App\Api\App\Object;

use App\Entity\Collection;

class CollectionObject
{
    public int $id;
    public string $name;
    public string $slug;
    public bool $is_public;
    public bool $is_owner;

    public function __construct(Collection $collection, ?int $currentUserId = null)
    {
        $this->id = $collection->getId();
        $this->name = $collection->getName();
        $this->slug = $collection->getSlug();
        $this->is_public = $collection->isPublic();
        $this->is_owner = $currentUserId ? $collection->getHyvorUserId() === $currentUserId : false;
    }
} 