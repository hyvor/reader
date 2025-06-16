<?php

namespace App\MessageHandler;

use App\Entity\Publication;
use App\Entity\PublicationFetch;
use App\Enum\FetchStatusEnum;
use App\Message\CheckAndFetchAllFeedsMessage;
use App\Repository\PublicationRepository;
use App\Service\FeedItemProcessor;
use App\Service\Parser\Parser;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
class CheckAndFetchAllFeedsHandler
{
    public function __construct(
        private PublicationRepository $publicationRepository,
        private EntityManagerInterface $entityManager,
        private FeedItemProcessor $itemProcessor,
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(CheckAndFetchAllFeedsMessage $message): void
    {
        $this->logger->info('Starting scheduled feed check');
        
        $duePublications = $this->publicationRepository->findDueForFetching(new \DateTime());
        
        if (empty($duePublications)) {
            $this->logger->debug('No publications due for fetching');
            return;
        }

        $this->logger->info('Found publications due for fetching', ['count' => count($duePublications)]);
        
        foreach ($duePublications as $publication) {
            $this->processFeed($publication);
        }
        
        $this->logger->info('Completed scheduled feed check');
    }

    private function processFeed(Publication $publication): void
    {
        $this->logger->info('Processing feed', [
            'publication_id' => $publication->getId(),
            'url' => $publication->getUrl()
        ]);

        $fetch = new PublicationFetch();
        $fetch->setPublication($publication);
        $fetch->setStatus(FetchStatusEnum::PENDING);
        $this->entityManager->persist($fetch);
        $this->entityManager->flush(); 

        $startTime = microtime(true);

        try {
            $headers = [];
            if ($etag = $publication->getConditionalGetEtag()) {
                $headers['If-None-Match'] = $etag;
            }
            if ($lastModified = $publication->getConditionalGetLastModified()) {
                $headers['If-Modified-Since'] = $lastModified;
            }

            $response = $this->httpClient->request('GET', $publication->getUrl(), [
                'headers' => $headers,
                'timeout' => 30,
                'max_redirects' => 5,
            ]);

            $statusCode = $response->getStatusCode();
            $latencyMs = (int)((microtime(true) - $startTime) * 1000);

            if ($statusCode === 304) {
                $fetch->setStatus(FetchStatusEnum::COMPLETED)
                      ->setStatusCode(304)
                      ->setLatencyMs($latencyMs)
                      ->setNewItemsCount(0)
                      ->setUpdatedItemsCount(0);

                $this->updateNextFetchTime($publication);
                $this->entityManager->flush();

                $this->logger->info('Feed not modified (304)', [
                    'publication_id' => $publication->getId()
                ]);
                return;
            }

            if ($statusCode !== 200) {
                throw new \RuntimeException("HTTP {$statusCode}: Failed to fetch feed");
            }

            $content = $response->getContent();
            $parser = new Parser($content);
            $feed = $parser->parse();

            $result = $this->itemProcessor->processItems($publication, $feed);

            $fetch->setStatus(FetchStatusEnum::COMPLETED)
                  ->setStatusCode($statusCode)
                  ->setLatencyMs($latencyMs)
                  ->setNewItemsCount($result['new_items'])
                  ->setUpdatedItemsCount($result['updated_items']);

            $publication->setLastFetchedAt(new \DateTime());
            
            $responseHeaders = $response->getHeaders();
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

            $this->updateNextFetchTime($publication);

            $this->logger->info('Successfully processed feed', [
                'publication_id' => $publication->getId(),
                'new_items' => $result['new_items'],
                'updated_items' => $result['updated_items'],
                'latency_ms' => $latencyMs
            ]);

        } catch (\Throwable $e) {
            $latencyMs = (int)((microtime(true) - $startTime) * 1000);
            
            $this->logger->error('Feed processing failed', [
                'publication_id' => $publication->getId(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $fetch->markAsFailed(
                error: substr($e->getMessage(), 0, 255), 
                errorPrivate: $e->getMessage() . "\n" . $e->getTraceAsString() 
            );
            $fetch->setLatencyMs($latencyMs);

            $this->updateNextFetchTime($publication);
        }

        $this->entityManager->flush();
    }

    private function updateNextFetchTime(Publication $publication): void
    {
        $nextFetchAt = new \DateTime();
        $nextFetchAt->modify("+{$publication->getInterval()} minutes");
        $publication->setNextFetchAt($nextFetchAt);
        $publication->setUpdatedAt(new \DateTime());
    }
} 