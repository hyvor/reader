<?php

namespace App\Tests\Service\Fetch;

use App\Entity\Collection;
use App\Entity\Publication;
use App\Entity\PublicationFetch;
use App\Service\Fetch\Message\FetchMessage;
use App\Service\Fetch\Message\ProcessFeedMessage;
use App\Service\Fetch\Handler\FetchHandler;
use App\Service\Fetch\Handler\ProcessFeedHandler;
use App\Service\Fetch\FetchStatusEnum;
use App\Service\Parser\Types\Feed;
use App\Service\Parser\Types\FeedType;
use App\Service\Parser\Types\Item;
use App\Service\Parser\Types\Author;
use App\Service\Parser\Types\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class FetchIntegrationTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private MessageBusInterface $messageBus;
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private FetchHandler $fetchHandler;
    private ProcessFeedHandler $processFeedHandler;
    private Collection $collection;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->logger = $container->get(LoggerInterface::class);
        
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        
        $fetchService = $container->get('App\Service\Fetch\FetchService');
        $publicationRepository = $container->get('App\Repository\PublicationRepository');
        
        $this->fetchHandler = new FetchHandler(
            $fetchService,
            $this->entityManager,
            $this->messageBus
        );
        
        $this->processFeedHandler = new ProcessFeedHandler(
            $fetchService,
            $publicationRepository,
            $this->entityManager,
            $this->httpClient,
            $this->logger
        );
        
        $this->entityManager->createQuery('DELETE FROM App\Entity\PublicationFetch')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Item')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Publication')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Collection')->execute();
        
        $this->collection = new Collection();
        $this->collection->setName('Test Collection');
        $this->entityManager->persist($this->collection);
        $this->entityManager->flush();
    }

    public function test_fetch_handler_with_no_due_publications(): void
    {
        $publication = $this->create_publication('https://example.com/feed.xml');
        $publication->setNextFetchAt(new \DateTimeImmutable('+1 hour'));
        $this->entityManager->flush();

        $this->messageBus->expects($this->never())->method('dispatch');

        $fetchMessage = new FetchMessage();
        $this->fetchHandler->__invoke($fetchMessage);

        $this->entityManager->refresh($publication);
        $this->assertGreaterThan(new \DateTimeImmutable(), $publication->getNextFetchAt());
    }

    public function test_fetch_handler_with_due_publications(): void
    {
        $publication1 = $this->create_publication('https://example1.com/feed.xml');
        $publication1->setNextFetchAt(new \DateTimeImmutable('-1 hour'));
        $publication1->setInterval(30);
        
        $publication2 = $this->create_publication('https://example2.com/feed.xml');
        $publication2->setNextFetchAt(new \DateTimeImmutable('-30 minutes'));
        $publication2->setInterval(60);
        
        $this->entityManager->flush();

        $dispatchedMessages = [];
        $this->messageBus->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnCallback(function ($message) use (&$dispatchedMessages) {
                $dispatchedMessages[] = $message;
                return new Envelope($message);
            });

        $fetchMessage = new FetchMessage();
        $this->fetchHandler->__invoke($fetchMessage);

        $this->assertCount(2, $dispatchedMessages);
        $this->assertInstanceOf(ProcessFeedMessage::class, $dispatchedMessages[0]);
        $this->assertInstanceOf(ProcessFeedMessage::class, $dispatchedMessages[1]);
        $this->assertEquals($publication1->getId(), $dispatchedMessages[0]->publicationId);
        $this->assertEquals($publication2->getId(), $dispatchedMessages[1]->publicationId);

        $this->entityManager->refresh($publication1);
        $this->entityManager->refresh($publication2);
        
        $this->assertGreaterThan(new \DateTimeImmutable(), $publication1->getNextFetchAt());
        $this->assertGreaterThan(new \DateTimeImmutable(), $publication2->getNextFetchAt());
    }

    public function test_process_feed_handler_with_non_existent_publication(): void
    {
        $message = new ProcessFeedMessage(99999);
        
        $this->processFeedHandler->__invoke($message);
        
        $fetchRecords = $this->entityManager->getRepository(PublicationFetch::class)->findAll();
        $this->assertEmpty($fetchRecords);
    }

    public function test_process_feed_handler_with_successful_response(): void
    {
        $publication = $this->create_publication('https://example.com/feed.xml');
        $this->entityManager->flush();
        
        $jsonFeedContent = json_encode([
            'version' => 'https://jsonfeed.org/version/1.1',
            'title' => 'Test Feed',
            'description' => 'Test feed description',
            'home_page_url' => 'https://example.com',
            'items' => [
                [
                    'id' => '1',
                    'url' => 'https://example.com/item1',
                    'title' => 'Test Item 1',
                    'summary' => 'Test item 1 description',
                    'date_published' => '2015-10-21T07:28:00Z'
                ]
            ]
        ]);
        
        $response = $this->create_mock_response(200, $jsonFeedContent, [
            'etag' => ['new-etag-value'],
            'last-modified' => ['Wed, 21 Oct 2015 07:28:00 GMT']
        ]);
        
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'https://example.com/feed.xml')
            ->willReturn($response);

        $message = new ProcessFeedMessage($publication->getId());
        $this->processFeedHandler->__invoke($message);

        $fetchRecords = $this->entityManager->getRepository(PublicationFetch::class)->findAll();
        $this->assertCount(1, $fetchRecords);
        
        $fetch = $fetchRecords[0];
        $this->assertEquals(FetchStatusEnum::COMPLETED, $fetch->getStatus());
        $this->assertEquals(200, $fetch->getStatusCode());
        $this->assertGreaterThanOrEqual(0, $fetch->getLatencyMs());
        
        $this->entityManager->refresh($publication);
        $this->assertEquals('new-etag-value', $publication->getConditionalGetEtag());
        $this->assertEquals('Wed, 21 Oct 2015 07:28:00 GMT', $publication->getConditionalGetLastModified());
        $this->assertNotNull($publication->getLastFetchedAt());
    }

    public function test_process_feed_handler_with_not_modified_response(): void
    {
        $publication = $this->create_publication('https://example.com/feed.xml');
        $publication->setConditionalGetEtag('existing-etag');
        $this->entityManager->flush();
        
        $response = $this->create_mock_response(304, '');
        
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'https://example.com/feed.xml', [
                'headers' => ['If-None-Match' => 'existing-etag'],
                'timeout' => 30,
                'max_redirects' => 5,
            ])
            ->willReturn($response);

        $message = new ProcessFeedMessage($publication->getId());
        $this->processFeedHandler->__invoke($message);

        $fetchRecords = $this->entityManager->getRepository(PublicationFetch::class)->findAll();
        $this->assertCount(1, $fetchRecords);
        
        $fetch = $fetchRecords[0];
        $this->assertEquals(FetchStatusEnum::COMPLETED, $fetch->getStatus());
        $this->assertEquals(304, $fetch->getStatusCode());
        $this->assertEquals(0, $fetch->getNewItemsCount());
        $this->assertEquals(0, $fetch->getUpdatedItemsCount());
    }

    public function test_process_feed_handler_with_error_status_code(): void
    {
        $publication = $this->create_publication('https://example.com/feed.xml');
        $this->entityManager->flush();
        
        $response = $this->create_mock_response(404, 'Not Found');
        
        $this->httpClient->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $message = new ProcessFeedMessage($publication->getId());
        $this->processFeedHandler->__invoke($message);

        $fetchRecords = $this->entityManager->getRepository(PublicationFetch::class)->findAll();
        $this->assertCount(1, $fetchRecords);
        
        $fetch = $fetchRecords[0];
        $this->assertEquals(FetchStatusEnum::FAILED, $fetch->getStatus());
        $this->assertEquals(404, $fetch->getStatusCode());
        $this->assertStringContainsString('404', $fetch->getError());
    }

    public function test_process_feed_handler_with_transport_exception(): void
    {
        $publication = $this->create_publication('https://example.com/feed.xml');
        $this->entityManager->flush();
        
        $exception = new class('Transport error occurred') extends \Exception implements TransportExceptionInterface {};
        
        $this->httpClient->expects($this->once())
            ->method('request')
            ->willThrowException($exception);

        $message = new ProcessFeedMessage($publication->getId());
        $this->processFeedHandler->__invoke($message);

        $fetchRecords = $this->entityManager->getRepository(PublicationFetch::class)->findAll();
        $this->assertCount(1, $fetchRecords);
        
        $fetch = $fetchRecords[0];
        $this->assertEquals(FetchStatusEnum::FAILED, $fetch->getStatus());
        $this->assertEquals(0, $fetch->getStatusCode());
        $this->assertNotEmpty($fetch->getError());
    }

    public function test_process_feed_handler_with_conditional_get_headers(): void
    {
        $publication = $this->create_publication('https://example.com/feed.xml');
        $publication->setConditionalGetEtag('old-etag');
        $publication->setConditionalGetLastModified('Tue, 20 Oct 2015 07:28:00 GMT');
        $this->entityManager->flush();
        
        $jsonFeedContent = json_encode([
            'version' => 'https://jsonfeed.org/version/1.1',
            'title' => 'Test Feed',
            'items' => []
        ]);
        $response = $this->create_mock_response(200, $jsonFeedContent);
        
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'https://example.com/feed.xml', [
                'headers' => [
                    'If-None-Match' => 'old-etag',
                    'If-Modified-Since' => 'Tue, 20 Oct 2015 07:28:00 GMT'
                ],
                'timeout' => 30,
                'max_redirects' => 5,
            ])
            ->willReturn($response);

        $message = new ProcessFeedMessage($publication->getId());
        $this->processFeedHandler->__invoke($message);

        $fetchRecords = $this->entityManager->getRepository(PublicationFetch::class)->findAll();
        $this->assertCount(1, $fetchRecords);
        $this->assertEquals(FetchStatusEnum::COMPLETED, $fetchRecords[0]->getStatus());
    }

    public function test_process_feed_handler_updates_feed_metadata(): void
    {
        $publication = $this->create_publication('https://example.com/feed.xml');
        $publication->setTitle('Old Title');
        $publication->setDescription('Old Description');
        $this->entityManager->flush();
        
        $jsonFeedContent = json_encode([
            'version' => 'https://jsonfeed.org/version/1.1',
            'title' => 'Updated Feed Title',
            'description' => 'Updated feed description',
            'home_page_url' => 'https://example.com',
            'items' => []
        ]);
        $response = $this->create_mock_response(200, $jsonFeedContent);
        
        $this->httpClient->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $message = new ProcessFeedMessage($publication->getId());
        $this->processFeedHandler->__invoke($message);

        $this->entityManager->refresh($publication);
        $this->assertEquals('Updated Feed Title', $publication->getTitle());
        $this->assertEquals('Updated feed description', $publication->getDescription());
    }

    private function create_publication(string $url): Publication
    {
        $publication = new Publication();
        $publication->setUrl($url);
        $publication->setCollection($this->collection);
        $publication->setNextFetchAt(new \DateTimeImmutable());
        
        $this->entityManager->persist($publication);
        
        return $publication;
    }

    private function create_mock_response(int $statusCode, string $content, array $headers = []): ResponseInterface
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($statusCode);
        $response->method('getContent')->willReturn($content);
        $response->method('getHeaders')->willReturn($headers);
        
        return $response;
    }


}
