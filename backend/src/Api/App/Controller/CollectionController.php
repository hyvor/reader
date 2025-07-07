<?php

namespace App\Api\App\Controller;

use App\Entity\Collection;
use App\Repository\CollectionRepository;
use App\Service\Collection\CollectionService;
use Hyvor\Internal\Auth\AuthUser;
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
        private readonly CollectionService $collectionService,
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function getCollections(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof AuthUser) {
            return $this->json(['error' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $collections = $this->collectionService->getUserCollections($user->id);

        $collectionsData = [];
        foreach ($collections as $collection) {
            $collectionsData[] = [
                'id' => $collection->getId(),
                'uuid' => $collection->getUuid(),
                'name' => $collection->getName(),
                'slug' => $collection->getSlug(),
                'is_public' => $collection->isPublic(),
                'is_owner' => $collection->getHyvorUserId() === $user->id,
            ];
        }

        return $this->json([
            'collections' => $collectionsData,
        ]);
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

    #[Route('/{slug}/join', name: 'join', methods: ['POST'])]
    public function joinCollection(string $slug): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof AuthUser) {
            return $this->json(['error' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $collectionUser = $this->collectionService->joinCollection($user->id, $slug);
            return $this->json([
                'message' => 'Successfully joined collection',
                'access' => [
                    'write_access' => $collectionUser->hasWriteAccess(),
                ]
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{slug}/leave', name: 'leave', methods: ['POST'])]
    public function leaveCollection(string $slug): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof AuthUser) {
            return $this->json(['error' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $this->collectionService->leaveCollection($user->id, $slug);
            return $this->json(['message' => 'Successfully left collection']);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

} 