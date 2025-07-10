<?php

namespace App\Api\App\Controller;

use App\Repository\ItemRepository;
use App\Repository\PublicationRepository;
use App\Repository\CollectionRepository;
use App\Api\App\Object\ItemObject;
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
        private readonly ItemRepository $itemRepository,
        private readonly PublicationRepository $publicationRepository,
        private readonly CollectionRepository $collectionRepository,
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
                $uuid = Uuid::fromString($publicationId);
            } catch (\InvalidArgumentException $e) {
                return $this->json(['error' => 'Invalid UUID format for publication_id'], Response::HTTP_BAD_REQUEST);
            }

            $publication = $this->publicationRepository->findOneBy(['uuid' => $uuid]);
            if (!$publication) {
                return $this->json(['error' => 'Publication not found'], Response::HTTP_NOT_FOUND);
            }

            $publicationItems = $publication->getItems()->slice($offset, $limit);
            foreach ($publicationItems as $item) {
                $items[] = new ItemObject($item);
            }
            
        } else if ($collectionId) {
            try {
                $uuid = Uuid::fromString($collectionId);
            } catch (\InvalidArgumentException $e) {
                return $this->json(['error' => 'Invalid UUID format for collection_id'], Response::HTTP_BAD_REQUEST);
            }

            $collection = $this->collectionRepository->findOneBy(['uuid' => $uuid]);
            if (!$collection) {
                return $this->json(['error' => 'Collection not found'], Response::HTTP_NOT_FOUND);
            }

            $allItems = [];
            foreach ($collection->getPublications() as $publication) {
                foreach ($publication->getItems() as $item) {
                    $allItems[] = new ItemObject($item);
                }
            }

            usort($allItems, function ($a, $b) {
                return ($b->published_at ?? 0) <=> ($a->published_at ?? 0);
            });

            $items = array_slice($allItems, $offset, $limit);
            
        } else {
            return $this->json(['error' => 'Either collection_id or publication_id parameter is required'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'items' => $items,
        ]);
    }
} 