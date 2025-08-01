<?php

namespace App\Api\App\Controller;

use App\Repository\CollectionRepository;
use App\Service\Publication\PublicationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/items', name: 'app_api_items_')]
class ItemController extends AbstractController
{
    public function __construct(
        private readonly CollectionRepository  $collectionRepository,
        private readonly PublicationService    $publicationService,
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function getItems(Request $request): JsonResponse
    {
        $collectionId = $request->query->get('collection_id');
        $publicationId = $request->query->get('publication_id');
        $limit = (int) $request->query->get('limit', 50);
        $offset = (int) $request->query->get('offset', 0);

        if ($limit > 100) {
            $limit = 100;
        }
        if ($limit < 1) {
            $limit = 1;
        }

        $items = [];
        
        if ($publicationId) {
            if (!Uuid::isValid($publicationId)) {
                return $this->json(['error' => 'Invalid UUID format for publication_id'], Response::HTTP_BAD_REQUEST);
            }

            $publication = $this->publicationService->findPublicationByUuid($publicationId);
            if (!$publication) {
                return $this->json(['error' => 'Publication not found'], Response::HTTP_NOT_FOUND);
            }

            $publicationItems = $publication->getItems()->slice($offset, $limit);
            foreach ($publicationItems as $item) {
                $items[] = $this->formatItem($item);
            }
            
        } else if ($collectionId) {
            if (!Uuid::isValid($collectionId)) {
                return $this->json(['error' => 'Invalid UUID format for collection_id'], Response::HTTP_BAD_REQUEST);
            }

            $collection = $this->collectionRepository->findOneBy(['uuid' => $collectionId]);
            if (!$collection) {
                return $this->json(['error' => 'Collection not found'], Response::HTTP_NOT_FOUND);
            }

            foreach ($collection->getPublications() as $publication) {
                foreach ($publication->getItems() as $item) {
                    $items[] = $this->formatItem($item);
                }
            }

            usort($items, function ($a, $b) {
                return ($b['published_at'] ?? 0) <=> ($a['published_at'] ?? 0);
            });

            $items = array_slice($items, $offset, $limit);
            
        } else {
            return $this->json(['error' => 'Either collection_id or publication_id parameter is required'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'items' => $items,
        ]);
    }

    private function formatItem($item): array
    {
        return [
            'id' => $item->getId(),
            'guid' => $item->getGuid(),
            'uuid' => $item->getUuid(),
            'title' => $item->getTitle() ?? 'Untitled',
            'url' => $item->getUrl(),
            'content_html' => $item->getContentHtml(),
            'content_text' => $item->getContentText(),
            'summary' => $item->getSummary(),
            'image' => $item->getImage(),
            'published_at' => $item->getPublishedAt()?->getTimestamp(),
            'updated_at' => $item->getUpdatedAt()?->getTimestamp(),
            'authors' => $item->getAuthors(),
            'tags' => $item->getTags(),
            'language' => $item->getLanguage(),
            'publication_id' => $item->getPublication()?->getId(),
            'publication_uuid' => $item->getPublication()?->getUuid(),
            'publication_title' => $item->getPublication()?->getTitle() ?? 'Untitled',
        ];
    }
} 