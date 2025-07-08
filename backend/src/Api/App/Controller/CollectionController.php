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

#[Route('/collections')]
class CollectionController extends AbstractController
{
    public function __construct(
        private readonly CollectionRepository $collectionRepository,
        private readonly CollectionService $collectionService,
    ) {
    }

    #[Route('', methods: ['GET'])]
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

    #[Route('/{slug}/join', methods: ['POST'])]
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

    #[Route('/{slug}/leave', methods: ['POST'])]
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

    #[Route('/{slug}', methods: ['GET'])]
    public function getCollectionBySlug(string $slug): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof AuthUser) {
            return $this->json(['error' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $collection = $this->collectionService->findBySlug($slug);

        if (!$collection) {
            return $this->json(['error' => 'Collection not found'], Response::HTTP_NOT_FOUND);
        }

        if (!$this->collectionService->hasUserAccess($user->id, $collection)) {
            return $this->json(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        return $this->json([
            'collection' => [
                'id' => $collection->getId(),
                'uuid' => $collection->getUuid(),
                'name' => $collection->getName(),
                'slug' => $collection->getSlug(),
                'is_public' => $collection->isPublic(),
                'is_owner' => $collection->getHyvorUserId() === $user->id,
            ]
        ]);
    }

    #[Route('/{slug}/publications', methods: ['GET'])]
    public function getCollectionPublications(string $slug): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof AuthUser) {
            return $this->json(['error' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $collection = $this->collectionService->findBySlug($slug);

        if (!$collection) {
            return $this->json(['error' => 'Collection not found'], Response::HTTP_NOT_FOUND);
        }

        if (!$this->collectionService->hasUserAccess($user->id, $collection)) {
            return $this->json(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $publications = [];
        foreach ($collection->getPublications() as $publication) {
            $publications[] = [
                'id' => $publication->getId(),
                'uuid' => $publication->getUuid(),
                'title' => $publication->getTitle() ?? 'Untitled',
                'url' => $publication->getUrl(),
                'description' => $publication->getDescription() ?? '',
                'subscribers' => $publication->getSubscribers(),
                'created_at' => $publication->getCreatedAt()->format('c'),
                'updated_at' => $publication->getUpdatedAt()->format('c'),
            ];
        }

        return $this->json([
            'collection' => [
                'id' => $collection->getId(),
                'uuid' => $collection->getUuid(),
                'name' => $collection->getName(),
                'slug' => $collection->getSlug(),
                'is_public' => $collection->isPublic(),
                'is_owner' => $collection->getHyvorUserId() === $user->id,
            ],
            'publications' => $publications,
        ]);
    }

    #[Route('/{slug}', methods: ['DELETE'])]
    public function deleteCollection(string $slug): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof AuthUser) {
            return $this->json(['error' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $this->collectionService->deleteCollection($user->id, $slug);
            return $this->json(['message' => 'Collection deleted successfully']);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

} 