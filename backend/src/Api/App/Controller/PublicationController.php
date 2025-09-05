<?php

namespace App\Api\App\Controller;

use App\Api\App\Authorization\AuthorizationListener;
use App\Service\Publication\PublicationService;
use App\Service\Collection\CollectionService;
use App\Service\Fetch\FetchService;
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
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use App\Api\App\Input\AddPublicationInput;
use App\Service\Parser\ParserException;
use App\Service\Fetch\Exception\UnexpectedStatusCodeException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class PublicationController extends AbstractController
{
    public function __construct(
        private readonly PublicationService $publicationService,
        private readonly CollectionService $collectionService,
        private readonly FetchService $fetchService,
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
    public function addPublication(
        #[MapRequestPayload] AddPublicationInput $payload,
        Request $request
    ): JsonResponse
    {
        $user = AuthorizationListener::getUser($request);
        $collectionSlug = trim($payload->collection_slug);
        $url = trim($payload->url);

        $collection = $this->collectionService->findBySlug($collectionSlug);
        if (!$collection) {
            throw new NotFoundHttpException('Collection not found');
        }

        if (!$this->collectionService->hasUserWriteAccess($user->id, $collection)) {
            throw new AccessDeniedHttpException('Write access required');
        }

        try {
            $inspection = $this->fetchService->inspectFeed($url);
        } catch (UnexpectedStatusCodeException $e) {
            throw new BadRequestHttpException('Feed returned HTTP ' . $e->getHttpCode());
        } catch (ParserException $e) {
            throw new BadRequestHttpException($e->getMessage());
        } catch (TransportExceptionInterface $e) {
            throw new BadRequestHttpException('Could not fetch the URL');
        }

        $normalizedUrl = $inspection['final_url'];
        $parsedTitle = $inspection['title'];
        $fetchedHeaders = $inspection['headers'];

        $publication = $this->publicationService->findByUrl($normalizedUrl);
        $created = false;
        $attached = false;

        if (!$publication) {
            $publication = $this->publicationService->createPublication($collection, $normalizedUrl, $parsedTitle);
            $created = true;
            $attached = true;

            if (isset($fetchedHeaders['etag'][0])) {
                $publication->setConditionalGetEtag($fetchedHeaders['etag'][0]);
            }
            if (isset($fetchedHeaders['last-modified'][0])) {
                $publication->setConditionalGetLastModified($fetchedHeaders['last-modified'][0]);
            }

            $result = $this->fetchService->processItems($publication, $inspection['feed']);
            if ($inspection['feed']->title && $publication->getTitle() !== $inspection['feed']->title) {
                $publication->setTitle($inspection['feed']->title);
            }
            if ($inspection['feed']->description && $publication->getDescription() !== $inspection['feed']->description) {
                $publication->setDescription($inspection['feed']->description);
            }
            $publication->setLastFetchedAt(new \DateTimeImmutable());
            $this->fetchService->updateNextFetchTime($publication);

            $this->em->flush();

            $status = Response::HTTP_CREATED;
        } else {
            $attached = $this->publicationService->attachToCollectionIfMissing($publication, $collection);

            if ($attached) {
                $publication->setIsFetching(true);
                $this->em->flush();
                $this->messageBus->dispatch(new ProcessFeedMessage($publication->getId()));
            }

            $status = Response::HTTP_OK;
        }

        return $this->json([
            'publication' => new \App\Api\App\Object\PublicationObject($publication),
            'created' => $created,
            'attached' => $attached,
        ], $status);
    }
}
