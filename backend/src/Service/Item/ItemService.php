<?php

namespace App\Service\Item;

use App\Entity\Publication;
use App\Repository\PublicationRepository;

class ItemService
{
    public function __construct(
        private readonly PublicationRepository $publicationRepository,
    ) {
    }

    public function findPublicationByUuid(string $publicationId): ?Publication
    {
        return $this->publicationRepository->findOneBy(['uuid' => $publicationId]);
    }
} 