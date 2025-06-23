<?php

namespace App\Service\Fetch\Handler;

use App\Entity\Publication;
use App\Entity\PublicationFetch;
use App\Repository\PublicationRepository;
use App\Service\Fetch\Message\ProcessFeedMessage;
use App\Service\Fetch\FetchService;
use App\Service\Fetch\FetchStatusEnum;
use App\Service\Parser\Parser;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use App\Service\Fetch\Exception\UnexpectedStatusCodeException;

#[AsMessageHandler]
class ProcessFeedHandler
{
    public function __construct(
        private FetchService $fetchService,
        private PublicationRepository $publicationRepository,
        private EntityManagerInterface $entityManager,
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(ProcessFeedMessage $message): void
    {
        $publication = $this->publicationRepository->find($message->publicationId);
        
        if (!$publication) {
            $this->logger->error('Publication not found', ['publication_id' => $message->publicationId]);
            return;
        }

        $fetch = $this->createFetch($publication);

        $startTime = microtime(true);

        try {
            $response = $this->requestFeed($publication);

            $statusCode = $response->getStatusCode();
            $latencyMs = (int)((microtime(true) - $startTime) * 1000);

            if ($statusCode === 304) {
                $this->handleNotModified($fetch, $latencyMs);
                $this->entityManager->flush();
                return;
            }

            if ($statusCode < 200 || $statusCode >= 300) {
                throw new UnexpectedStatusCodeException($statusCode);
            }

            $feed = (new Parser($response->getContent()))->parse();
            $result = $this->fetchService->processItems($publication, $feed);

            $this->markFetchCompleted($fetch, $statusCode, $latencyMs, $result);
            $this->updatePublication($publication, $response->getHeaders(), $feed);

        } catch (TransportExceptionInterface|UnexpectedStatusCodeException $e) {
            $latencyMs = (int)((microtime(true) - $startTime) * 1000);
            $status = $e instanceof UnexpectedStatusCodeException ? $e->getHttpCode() : 0;

            $this->logger->error('Feed processing failed', [
                'publication_id' => $publication->getId(),
                'error' => $e->getMessage(),
                'status_code' => $status,
                'trace' => $e->getTraceAsString(),
            ]);

            $fetch->setStatus(FetchStatusEnum::FAILED);
            $fetch->setError(substr($e->getMessage(), 0, 255));
            $fetch->setErrorPrivate($e->getMessage() . "\n" . $e->getTraceAsString());
            $fetch->setStatusCode($status)
                  ->setLatencyMs($latencyMs);
        }

        $this->entityManager->flush();
    }

    private function createFetch(Publication $publication): PublicationFetch
    {
        $fetch = new PublicationFetch();
        $fetch->setPublication($publication);
        $fetch->setStatus(FetchStatusEnum::PENDING);

        $this->entityManager->persist($fetch);

        return $fetch;
    }

    private function requestFeed(Publication $publication)
    {
        $headers = [];

        if ($etag = $publication->getConditionalGetEtag()) {
            $headers['If-None-Match'] = $etag;
        }

        if ($lastModified = $publication->getConditionalGetLastModified()) {
            $headers['If-Modified-Since'] = $lastModified;
        }

        return $this->httpClient->request('GET', $publication->getUrl(), [
            'headers'       => $headers,
            'timeout'       => 30,
            'max_redirects' => 5,
        ]);
    }

    private function handleNotModified(PublicationFetch $fetch, int $latencyMs): void
    {
        $fetch->setStatus(FetchStatusEnum::COMPLETED);
        $fetch->setStatusCode(304)
              ->setLatencyMs($latencyMs)
              ->setNewItemsCount(0)
              ->setUpdatedItemsCount(0);
    }

    private function markFetchCompleted(PublicationFetch $fetch, int $statusCode, int $latencyMs, array $result): void
    {
        $fetch->setStatus(FetchStatusEnum::COMPLETED);
        $fetch->setStatusCode($statusCode)
              ->setLatencyMs($latencyMs)
              ->setNewItemsCount($result['new_items'] ?? 0)
              ->setUpdatedItemsCount($result['updated_items'] ?? 0);
    }

    private function updatePublication(Publication $publication, array $responseHeaders, $feed): void
    {
        $publication->setLastFetchedAt(new \DateTimeImmutable());

        if (isset($responseHeaders['etag'][0])) {
            $publication->setConditionalGetEtag($responseHeaders['etag'][0]);
        }

        if (isset($responseHeaders['last-modified'][0])) {
            $publication->setConditionalGetLastModified($responseHeaders['last-modified'][0]);
        }

        if ($feed->title && $publication->getTitle() !== $feed->title) {
            $publication->setTitle($feed->title);
        }

        if ($feed->description && $publication->getDescription() !== $feed->description) {
            $publication->setDescription($feed->description);
        }
    }
} 