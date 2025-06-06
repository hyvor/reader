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

#[Route('/init', name: 'app_api_init_')]
class InitController extends AbstractController
{
    public function __construct(
        private readonly ItemRepository $itemRepository,
        private readonly PublicationRepository $publicationRepository,
        private readonly CollectionRepository $collectionRepository,
    ) {
    }

    #[Route('', name: 'default', methods: ['GET'])]
    public function getInit(Request $request): JsonResponse
    {
        $data = [
            "collections" => [],
            "selCollection" => [],
            "selPublication" => [],
            "selPublicationItems" =>  []
        ];

        $collections = $this->collectionRepository->findAll();
        $data["collections"] = $collections;
        if (count($collections) > 0) {
            $currentCollection = $collections[0];

            $data["selCollection"] = [
                'id' => $currentCollection->getId(),
                'name' => $currentCollection->getName(),
                'uuid' => $currentCollection->getUuid()->toRfc4122(),
            ];

            $publications = $currentCollection->getPublications();
            /** @var Publication|false $currentPublication */
            $currentPublication = $publications->first();

            if ($currentPublication instanceof Publication) {
                $data["selPublication"] = [
                    'id' => $currentPublication->getId(),
                    'uuid' => $currentPublication->getUuid()->toRfc4122(),
                    'title' => $currentPublication->getTitle() ?? 'Untitled',
                    'url' => $currentPublication->getUrl(),
                    'description' => $currentPublication->getDescription() ?? '',
                    'subscribers' => $currentPublication->getSubscribers(),
                    'created_at' => $currentPublication->getCreatedAt()->getTimestamp(),
                    'updated_at' => $currentPublication->getUpdatedAt()->getTimestamp(),
                ];

                foreach ($currentPublication->getItems() as $item) {
                    $data["selPublicationItems"][] = [
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
