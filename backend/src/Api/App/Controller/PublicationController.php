<?php

namespace App\Api\App\Controller;

use App\Api\App\Object\PublicationObject;
use App\Service\Publication\PublicationService;
use App\Service\Collection\CollectionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Fetch\Message\ProcessFeedMessage;
use App\Api\App\Authorization\AuthorizationListener;

class PublicationController extends AbstractController
{
    public function __construct(
        private readonly PublicationService $publicationService,
        private readonly CollectionService $collectionService,
        private readonly MessageBusInterface $messageBus,
        private readonly EntityManagerInterface $em,
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

    #[Route('/publications', methods: ['POST'])]
    public function addPublication(Request $request): JsonResponse
    {
        $user = AuthorizationListener::getUser($request);

        $data = json_decode($request->getContent() ?: 'null', true);
        if (!is_array($data)) {
            throw new BadRequestHttpException('Invalid JSON body');
        }

        $collectionSlug = trim(strval($data['collection_slug'] ?? ''));
        $url = trim(strval($data['url'] ?? ''));
        $title = trim(strval($data['title'] ?? ''));

        if ($collectionSlug === '' || $url === '') {
            throw new BadRequestHttpException('collection_slug and url are required');
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new BadRequestHttpException('Invalid url');
        }

        $collection = $this->collectionService->findBySlug($collectionSlug);
        if (!$collection) {
            throw new NotFoundHttpException('Collection not found');
        }

        if (!$this->collectionService->hasUserWriteAccess($user->id, $collection)) {
            throw new AccessDeniedHttpException('Write access required');
        }

        $publication = $this->publicationService->findByUrl($url);
        $created = false;

        if (!$publication) {
            $publication = $this->publicationService->addPublication($collection, $url, $title ?: null);
            $created = true;
            $attached = true;
            $status = Response::HTTP_CREATED;
        } else {
            $attached = $this->publicationService->attachToCollectionIfMissing($publication, $collection);
            $status = Response::HTTP_OK;
        }

        if ($created || $attached) {
            $publication->setIsFetching(true);
            $this->em->flush();
            $this->messageBus->dispatch(new ProcessFeedMessage($publication->getId()));
        }

        return $this->json([
            'publication' => new PublicationObject($publication),
            'created' => $created,
            'attached' => $attached,
        ], $status);
    }
}
