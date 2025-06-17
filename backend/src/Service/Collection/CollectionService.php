<?php

namespace App\Service\Collection;

use App\Entity\Collection;
use Doctrine\ORM\EntityManagerInterface;

class CollectionService
{

    public function __construct(
        private EntityManagerInterface $em
    )
    {
    }

    /**
     * @return Collection[]
     */
    public function getUserCollections(): array
    {
        return $this->em->getRepository(Collection::class)->findAll(); // TODO: find By user
    }

}