<?php

namespace App\Api\App\Controller;

use App\Api\App\Object\CollectionObject;
use App\Api\App\Object\PublicationObject;
use App\Api\App\Object\ItemObject;
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
        $data["collections"] = array_map(fn($collection) => new CollectionObject($collection), $collections);

        $data["selectedCollection"] = $data["collections"][0];

        if (count($collections) > 0) {
            $publications = $collections[0]->getPublications();
            $data["publications"] = array_map(fn($publication) => new PublicationObject($publication), $publications->toArray());

            if (count($publications) > 0) {
                $items = [];
                foreach ($publications as $publication) {
                    $items = array_merge($items, $publication->getItems()->toArray());
                }
                
                $data["items"] = array_map(fn($item) => new ItemObject($item), $items);
            }
        }

        return $this->json($data);
    }
} 
