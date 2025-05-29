<?php

namespace App\Api\App\Controller;

use App\Entity\Collection;
use App\Repository\CollectionRepository;
use App\Repository\ItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api/app/collections', name: 'app_api_collections_')]
class CollectionController extends AbstractController
{
    public function __construct(
        private readonly CollectionRepository $collectionRepository,
        private readonly ItemRepository $itemRepository,
    ) {
    }

    #[Route('/{uuid}', name: 'get', methods: ['GET'])]
    public function getCollection(string $uuid): JsonResponse
    {
        try {
            $uuid = Uuid::fromString($uuid);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => 'Invalid UUID format'], Response::HTTP_BAD_REQUEST);
        }

        $collection = $this->collectionRepository->findOneBy(['uuid' => $uuid]);

        if (!$collection) {
            return $this->json(['error' => 'Collection not found'], Response::HTTP_NOT_FOUND);
        }

        // Get the publications in this collection
        $publications = [];
        foreach ($collection->getPublications() as $publication) {
            $publications[] = [
                'id' => $publication->getId(),
                'title' => $publication->getTitle() ?? 'Untitled',
                'url' => $publication->getUrl(),
                'description' => $publication->getDescription() ?? '',
                'subscribers' => $publication->getSubscribers(),
                'uuid' => $publication->getUuid()->toRfc4122(),
            ];
        }

        return $this->json([
            'collection' => [
                'id' => $collection->getId(),
                'name' => $collection->getName(),
                'uuid' => $collection->getUuid()->toRfc4122(),
            ],
            'publications' => $publications,
        ]);
    }

    #[Route('/{uuid}/items', name: 'get_items', methods: ['GET'])]
    public function getCollectionItems(string $uuid, Request $request): JsonResponse
    {
        try {
            $uuid = Uuid::fromString($uuid);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => 'Invalid UUID format'], Response::HTTP_BAD_REQUEST);
        }

        $collection = $this->collectionRepository->findOneBy(['uuid' => $uuid]);

        if (!$collection) {
            return $this->json(['error' => 'Collection not found'], Response::HTTP_NOT_FOUND);
        }

        // Get items for all publications in this collection
        $items = [];
        foreach ($collection->getPublications() as $publication) {
            foreach ($publication->getItems() as $item) {
                $items[] = [
                    'id' => $item->getId(),
                    'title' => $item->getTitle() ?? 'Untitled',
                    'url' => $item->getUrl(),
                    'content' => $item->getContent(),
                    'publication_id' => $publication->getId(),
                    'publication_title' => $publication->getTitle() ?? 'Untitled',
                    'published_at' => $item->getPublishedAt()->getTimestamp(),
                    'uuid' => $item->getUuid()->toRfc4122(),
                ];
            }
        }

        // Sort items by published_at (newest first)
        usort($items, function ($a, $b) {
            return $b['published_at'] - $a['published_at'];
        });

        return $this->json([
            'items' => $items,
        ]);
    }
} 