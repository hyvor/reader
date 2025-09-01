<?php

namespace App\Api\App\Controller;

use App\Api\App\Object\CollectionObject;
use App\Service\Collection\CollectionService;
 
use App\Api\App\Authorization\AuthorizationListener;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
 
 

class InitController extends AbstractController
{
    public function __construct(
        private CollectionService $collectionService
    ) {
    }

    #[Route('/init', methods: ['GET'])]
    public function getInit(Request $request): JsonResponse
    {
        $user = AuthorizationListener::getUser($request);

        $this->collectionService->ensureUserHasDefaultCollection($user);

        $collections = $this->collectionService->getUserCollections($user->id);

        return $this->json([
            'collections' => array_map(fn($collection) => new CollectionObject($collection, $user->id), $collections),
        ]);
    }
} 
