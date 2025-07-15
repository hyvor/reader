<?php

namespace App\Api\App\Controller;

use App\Service\Publication\PublicationService;
use App\Service\Collection\CollectionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/publications')]
class PublicationController extends AbstractController
{
    public function __construct(
        private readonly PublicationService $publicationService,
        private readonly CollectionService $collectionService,
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function getPublications(Request $request): JsonResponse
    {
        $collectionSlug = $request->query->get('collection_slug');
        
        if (!$collectionSlug) {
            return $this->json(['error' => 'collection_slug parameter is required'], Response::HTTP_BAD_REQUEST);
        }

        $collection = $this->collectionService->findBySlug($collectionSlug);
        if (!$collection) {
            return $this->json(['error' => 'Collection not found'], Response::HTTP_NOT_FOUND);
        }

        $publications = $this->publicationService->getPublicationsFromCollection($collection);

        return $this->json([
            'publications' => $publications,
        ]);
    }
} 