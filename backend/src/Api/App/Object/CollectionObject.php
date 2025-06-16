<?php

namespace App\Api\App\Object;

use App\Entity\Collection;

class CollectionObject
{
    public int $id;
    public string $name;
    public string $uuid;

    public function __construct(Collection $collection)
    {
        $this->id = $collection->getId();
        $this->name = $collection->getName();
        $this->uuid = $collection->getUuid()->toRfc4122();
    }
} 