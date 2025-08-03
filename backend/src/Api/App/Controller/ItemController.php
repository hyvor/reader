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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ItemController extends AbstractController
{
    public function __construct(
        private readonly ItemService $itemService,
        private readonly PublicationService $publicationService,
        private readonly CollectionService $collectionService,
    ) {
    }

    #[Route('/items', methods: ['GET'])]
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
                throw new NotFoundHttpException('Publication not found');
            }

            $items = $this->itemService->getItemsFromPublication($publication, $limit, $offset);
            
        } else if ($collectionSlug) {
            $collection = $this->collectionService->findBySlug($collectionSlug);
            if (!$collection) {
                throw new NotFoundHttpException('Collection not found');
            }

            $items = $this->itemService->getItemsFromCollection($collection, $limit, $offset);
            
        } else {
            throw new BadRequestHttpException('Either collection_id or publication_id parameter is required');
        }

        return $this->json([
            'items' => $items,
        ]);
    }
} 