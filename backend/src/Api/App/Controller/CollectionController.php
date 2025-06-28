<?php

namespace App\Api\App\Controller;

use App\Entity\Collection;
use App\Repository\CollectionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/collections', name: 'app_api_collections_')]
class CollectionController extends AbstractController
{
    public function __construct(
        private readonly CollectionRepository $collectionRepository,
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function getCollections(): JsonResponse
    {
        $collections = $this->collectionRepository->findAll();

        $collectionsData = [];
        foreach ($collections as $collection) {
            $collectionsData[] = [
                'id' => $collection->getId(),
                'uuid' => $collection->getUuid(),
                'name' => $collection->getName(),
            ];
        }

        return $this->json([
            'collections' => $collectionsData,
        ]);
    }

    #[Route('/{uuid}', name: 'get', methods: ['GET'])]
    public function getCollection(string $uuid): JsonResponse
    {
        if (!Uuid::isValid($uuid)) {
            return $this->json(['error' => 'Invalid UUID format'], Response::HTTP_BAD_REQUEST);
        }

        $collection = $this->collectionRepository->findOneBy(['uuid' => $uuid]);

        if (!$collection) {
            return $this->json(['error' => 'Collection not found'], Response::HTTP_NOT_FOUND);
        }

        $publications = [];
        foreach ($collection->getPublications() as $publication) {
            $publications[] = [
                'id' => $publication->getId(),
                'title' => $publication->getTitle() ?? 'Untitled',
                'url' => $publication->getUrl(),
                'description' => $publication->getDescription() ?? '',
                'subscribers' => $publication->getSubscribers(),
                'uuid' => $publication->getUuid(),
            ];
        }

        return $this->json([
            'collection' => [
                'id' => $collection->getId(),
                'name' => $collection->getName(),
                'uuid' => $collection->getUuid(),
            ],
            'publications' => $publications,
        ]);
    }


} 