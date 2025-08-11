<?php

namespace App\Api\App\Controller;

use App\Service\Publication\PublicationService;
use App\Service\Collection\CollectionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PublicationController extends AbstractController
{
    public function __construct(
        private readonly PublicationService $publicationService,
        private readonly CollectionService $collectionService,
    ) {
    }

    #[Route('/publications', methods: ['GET'])]
    public function getPublications(Request $request): JsonResponse
    {
        /** @var string|null $collectionSlug */
        $collectionSlug = $request->query->get('collection_slug');
        
        if (!$collectionSlug) {
            throw new BadRequestHttpException('collection_slug parameter is required');
        }

        $collection = $this->collectionService->findBySlug($collectionSlug);
        if (!$collection) {
            throw new NotFoundHttpException('Collection not found');
        }

        $publications = $this->publicationService->getPublicationsFromCollection($collection);

        return $this->json([
            'publications' => $publications,
        ]);
    }
} 
