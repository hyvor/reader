<?php

namespace App\Api\App\Controller;

use App\Api\App\Object\CollectionObject;
use App\Api\App\Object\PublicationObject;
use App\Api\App\Object\ItemObject;
use App\Repository\ItemRepository;
use App\Repository\PublicationRepository;
use App\Repository\CollectionRepository;
use App\Service\Collection\CollectionService;
use Hyvor\Internal\Auth\AuthUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class InitController extends AbstractController
{
    public function __construct(
        private readonly ItemRepository $itemRepository,
        private readonly PublicationRepository $publicationRepository,
        private readonly CollectionRepository $collectionRepository,
        private CollectionService $collectionService
    ) {
    }

    #[Route('/init', methods: ['GET'])]
    public function getInit(#[CurrentUser] AuthUser $user, Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof AuthUser) {
            return $this->json(['error' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $this->collectionService->ensureUserHasCollection($user);

        $data = [
            "collections" => [],
            "publications" => [],
            "items" =>  [],
            "selectedCollection" => null,
            "selectedPublication" => null
        ];

<<<<<<< Updated upstream
        $collections = $this->collectionService->getUserCollections($user);

        // $data["selectedCollection"] = $data["collections"][0];
=======
        $collections = $this->collectionService->getUserCollections($user->id);
>>>>>>> Stashed changes

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

        return $this->json([
            'collections' => array_map(fn($collection) => new CollectionObject($collection, $user->id), $collections)
        ] + $data);
    }
} 
