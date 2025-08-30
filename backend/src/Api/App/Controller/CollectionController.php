<?php

namespace App\Api\App\Controller;

use App\Api\App\Object\CollectionObject;
use App\Api\App\Object\PublicationObject;
use App\Service\Collection\CollectionService;
use Hyvor\Internal\Auth\AuthUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CollectionController extends AbstractController
{
    public function __construct(
        private readonly CollectionService $collectionService,
    ) {
    }

    #[Route('/collections', methods: ['GET'])]
    public function getCollections(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof AuthUser) {
            throw new AccessDeniedHttpException('Authentication required');
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
            throw new AccessDeniedHttpException('Authentication required');
        }

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

    #[Route('/collections', methods: ['POST'])]
    public function createCollection(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof AuthUser) {
            throw new AccessDeniedHttpException('Authentication required');
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            throw new BadRequestHttpException('Invalid JSON body');
        }

        $name = trim((string)($data['name'] ?? ''));
        if ($name === '') {
            throw new BadRequestHttpException('name is required');
        }

        $isPublic = false;
        if (array_key_exists('is_public', $data)) {
            if (!is_bool($data['is_public'])) {
                throw new BadRequestHttpException('is_public must be boolean');
            }
            $isPublic = $data['is_public'];
        }

        $collection = $this->collectionService->createCollection($user->id, $name, $isPublic);

        return $this->json([
            'collection' => new CollectionObject($collection, $user->id),
        ]);
    }


} 