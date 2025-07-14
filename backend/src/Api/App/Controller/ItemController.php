<?php

namespace App\Api\App\Controller;

use App\Service\Item\ItemService;
use App\Service\Publication\PublicationService;
use App\Service\Collection\CollectionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/items')]
class ItemController extends AbstractController
{
    public function __construct(
        private readonly ItemService $itemService,
        private readonly PublicationService $publicationService,
        private readonly CollectionService $collectionService,
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function getItems(Request $request): JsonResponse
    {
        $collectionId = $request->query->get('collection_id');
        $publicationId = $request->query->get('publication_id');
        $limit = (int) $request->query->get('limit', 50);
        $offset = (int) $request->query->get('offset', 0);

        if ($limit > 100) {
            $limit = 100;
        }
        if ($limit < 1) {
            $limit = 1;
        }

        $items = [];
        
        if ($publicationId) {
            try {
                Uuid::fromString($publicationId);
            } catch (\InvalidArgumentException $e) {
                return $this->json(['error' => 'Invalid UUID format for publication_id'], Response::HTTP_BAD_REQUEST);
            }

            $publication = $this->publicationService->findByUuid($publicationId);
            if (!$publication) {
                return $this->json(['error' => 'Publication not found'], Response::HTTP_NOT_FOUND);
            }

            $items = $this->itemService->getItemsFromPublication($publicationId, $limit, $offset);
            
        } else if ($collectionId) {
            try {
                Uuid::fromString($collectionId);
            } catch (\InvalidArgumentException $e) {
                return $this->json(['error' => 'Invalid UUID format for collection_id'], Response::HTTP_BAD_REQUEST);
            }

            $collection = $this->collectionService->findByUuid($collectionId);
            if (!$collection) {
                return $this->json(['error' => 'Collection not found'], Response::HTTP_NOT_FOUND);
            }

            $items = $this->itemService->getItemsFromCollection($collectionId, $limit, $offset);
            
        } else {
            return $this->json(['error' => 'Either collection_id or publication_id parameter is required'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'items' => $items,
        ]);
    }
} 