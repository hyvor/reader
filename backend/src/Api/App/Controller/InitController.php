<?php

namespace App\Api\App\Controller;

use App\Entity\Publication;
use App\Repository\ItemRepository;
use App\Repository\PublicationRepository;
use App\Repository\CollectionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class InitController extends AbstractController
{
    public function __construct(
        private readonly ItemRepository $itemRepository,
        private readonly PublicationRepository $publicationRepository,
        private readonly CollectionRepository $collectionRepository,
    ) {
    }

    #[Route('/init', methods: ['GET'])]
    public function getInit(Request $request): JsonResponse
    {
        $data = [
            "collections" => [],
            "publications" => [],
            "items" =>  [],
            "selectedCollection" => null,
            "selectedPublication" => null
        ];

        $collections = $this->collectionRepository->findAll();
        foreach ($collections as $collection) {
            $data["collections"][] = [
                'id' => $collection->getId(),
                'name' => $collection->getName(),
                'uuid' => $collection->getUuid()->toRfc4122(),
            ];
        }

        $data["selectedCollection"] = $data["collections"][0];

        if (count($collections) > 0) {
            $publications = $collections[0]->getPublications();
            foreach ($publications as $publication) {
                $data["publications"][] = [
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

            if (count($publications) > 0) {
                $items = [];
                foreach ($publications as $publication) {
                    $items = array_merge($items, $publication->getItems()->toArray());
                }

                foreach ($items as $item) {
                    $data["items"][] = [
                        'id' => $item->getId(),
                        'uuid' => $item->getUuid()->toRfc4122(),
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
                        'publication_uuid' => $item->getPublication()?->getUuid()->toRfc4122(),
                        'publication_title' => $item->getPublication()?->getTitle() ?? 'Untitled',
                    ];
                }
            }
        }

        return $this->json($data);
    }
} 
