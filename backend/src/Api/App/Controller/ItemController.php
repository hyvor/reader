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
        $collectionSlug = $request->query->get('collection_slug');
        $publicationSlug = $request->query->get('publication_slug');
        $limit = (int) $request->query->get('limit', 50);
        $offset = (int) $request->query->get('offset', 0);

        if ($limit > 100) {
            $limit = 100;
        }
        if ($limit < 1) {
            $limit = 1;
        }

        $items = [];
        
        if ($publicationSlug) {
            $publication = $this->publicationService->findBySlug($publicationSlug);
            if (!$publication) {
                return $this->json(['error' => 'Publication not found'], Response::HTTP_NOT_FOUND);
            }

            $items = $this->itemService->getItemsFromPublication($publicationSlug, $limit, $offset);
            
        } else if ($collectionSlug) {
            $collection = $this->collectionService->findBySlug($collectionSlug);
            if (!$collection) {
                return $this->json(['error' => 'Collection not found'], Response::HTTP_NOT_FOUND);
            }

            $items = $this->itemService->getItemsFromCollection($collectionSlug, $limit, $offset);
            
        } else {
            return $this->json(['error' => 'Either collection_id or publication_id parameter is required'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'items' => $items,
        ]);
    }
} 