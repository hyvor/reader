<?php

namespace App\Api\App\Controller;

use App\Api\App\Object\CollectionObject;
use App\Api\App\Object\PublicationObject;
use App\Repository\CollectionRepository;
use App\Service\Collection\CollectionService;
use Hyvor\Internal\Auth\AuthUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;

class CollectionController extends AbstractController
{
    public function __construct(
        private readonly CollectionRepository $collectionRepository,
        private readonly CollectionService $collectionService,
    ) {
    }

    #[Route('/collections', methods: ['GET'])]
    public function getCollections(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof AuthUser) {
            return $this->json(['error' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $collections = $this->collectionService->getUserCollections($user->id);

        return $this->json([
            'collections' => array_map(fn($collection) => new CollectionObject($collection, $user->id), $collections),
        ]);
    }

    #[Route('/collections/{slug}', methods: ['GET'])]
    public function getCollection(string $slug): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof AuthUser) {
            return $this->json(['error' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $collection = $this->collectionService->findBySlug($slug);

        if (!$collection) {
            throw new NotFoundHttpException('Collection not found');
            // return $this->json(['error' => 'Collection not found'], Response::HTTP_NOT_FOUND);
        }

        if (!$this->collectionService->hasUserReadAccess($user->id, $collection)) {
            return $this->json(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        return $this->json([
            'collection' => new CollectionObject($collection, $user->id),
            'publications' => array_map(fn($publication) => new PublicationObject($publication), $collection->getPublications()->toArray()),
        ]);
    }


} 