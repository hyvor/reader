<?php

namespace App\Api\App\Controller;

use App\Api\App\Object\CollectionObject;
use App\Service\Collection\CollectionService;
use Hyvor\Internal\Auth\AuthUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class InitController extends AbstractController
{
    public function __construct(
        private CollectionService $collectionService
    ) {
    }

    #[Route('/init', methods: ['GET'])]
    public function getInit(#[CurrentUser] AuthUser $user, Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof AuthUser) {
            throw new AccessDeniedHttpException('Authentication required');
        }

        $this->collectionService->ensureUserHasDefaultCollection($user);

        $collections = $this->collectionService->getUserCollections($user->id);

        return $this->json([
            'collections' => array_map(fn($collection) => new CollectionObject($collection, $user->id), $collections),
        ]);
    }
} 
