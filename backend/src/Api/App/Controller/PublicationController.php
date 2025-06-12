<?php

namespace App\Api\App\Controller;

use App\Repository\PublicationRepository;
use App\Repository\CollectionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/publications', name: 'app_api_publications_')]
class PublicationController extends AbstractController
{
    public function __construct(
        private readonly PublicationRepository $publicationRepository,
        private readonly CollectionRepository $collectionRepository,
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function getPublications(Request $request): JsonResponse
    {
        $collectionId = $request->query->get('collection_id');
        
        if (!$collectionId) {
            return $this->json(['error' => 'collection_id parameter is required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $uuid = Uuid::fromString($collectionId);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => 'Invalid UUID format for collection_id'], Response::HTTP_BAD_REQUEST);
        }

        $collection = $this->collectionRepository->findOneBy(['uuid' => $uuid]);

        if (!$collection) {
            return $this->json(['error' => 'Collection not found'], Response::HTTP_NOT_FOUND);
        }

        $publications = [];
        foreach ($collection->getPublications() as $publication) {
            $publications[] = [
                'id' => $publication->getId(),
                'uuid' => $publication->getUuid()->toRfc4122(),
                'title' => $publication->getTitle() ?? 'Untitled',
                'url' => $publication->getUrl(),
                'description' => $publication->getDescription() ?? '',
                'subscribers' => $publication->getSubscribers(),
                'created_at' => $publication->getCreatedAt()->getTimestamp(),
                'updated_at' => $publication->getUpdatedAt()->getTimestamp(),
            ];
        }

        return $this->json([
            'publications' => $publications,
        ]);
    }
} 