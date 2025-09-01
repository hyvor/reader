<?php

namespace App\Api\App\Controller;

use App\Api\App\Object\CollectionObject;
use App\Api\App\Object\PublicationObject;
use App\Service\Collection\CollectionService;
 
use Symfony\Component\HttpFoundation\Request;
use App\Api\App\Authorization\AuthorizationListener;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
 

class CollectionController extends AbstractController
{
    public function __construct(
        private readonly CollectionService $collectionService,
    ) {
    }

    #[Route('/collections', methods: ['GET'])]
    public function getCollections(Request $request): JsonResponse
    {
        $user = AuthorizationListener::getUser($request);

        $collections = $this->collectionService->getUserCollections($user->id);

        return $this->json([
            'collections' => array_map(fn($collection) => new CollectionObject($collection, $user->id), $collections),
        ]);
    }

    #[Route('/collections/{slug}', methods: ['GET'])]
    public function getCollection(string $slug, Request $request): JsonResponse
    {
        $user = AuthorizationListener::getUser($request);

        $collection = $this->collectionService->findBySlug($slug);

        if (!$collection) {
            throw new NotFoundHttpException('Collection not found');
        }

        if (!$this->collectionService->hasUserReadAccess($user->id, $collection)) {
            throw new AccessDeniedHttpException('Access denied');
        }

        return $this->json([
            'collection' => new CollectionObject($collection, $user->id),
            'publications' => array_map(fn($publication) => new PublicationObject($publication), $collection->getPublications()->toArray()),
        ]);
    }


} 