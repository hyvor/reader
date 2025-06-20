<?php

namespace App\Tests\Service\Fetch;

use App\Entity\PublicationFetch;
use App\Factory\CollectionFactory;
use App\Factory\PublicationFactory;
use App\Service\Fetch\FetchService;
use App\Service\Fetch\FetchStatusEnum;
use App\Service\Fetch\Handler\ProcessFeedHandler;
use App\Service\Fetch\Message\FetchMessage;
use App\Service\Fetch\Message\ProcessFeedMessage;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Psr\Log\LoggerInterface;
use App\Repository\PublicationRepository;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;
use Symfony\Component\HttpClient\Exception\TransportException;

#[CoversClass(ProcessFeedHandler::class)]
#[CoversClass(\App\Service\Fetch\Handler\FetchHandler::class)]
class FetchIntegrationTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    private EntityManagerInterface $entityManager;
    private MessageBusInterface $bus;
    private InMemoryTransport $asyncTransport;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->bus = self::getContainer()->get(MessageBusInterface::class);

        /** @var InMemoryTransport $transport */
        $transport = self::getContainer()->get('messenger.transport.async');
        $this->asyncTransport = $transport;
    }

    public function test_no_due_publications_dispatches_nothing(): void
    {
        $collection = CollectionFactory::new()->create()->_real();
        PublicationFactory::new([
            'collection'  => $collection,
            'nextFetchAt' => (new \DateTimeImmutable())->modify('+1 hour'),
        ])->create();

        $this->bus->dispatch(new FetchMessage());

        $this->assertCount(0, $this->asyncTransport->get(), 'ProcessFeedMessage should not have been dispatched.');
    }

    public function test_not_modified_response_marks_fetch_completed(): void
    {
        $collection = CollectionFactory::new()->create()->_real();
        $publication = PublicationFactory::new([
            'collection'  => $collection,
            'nextFetchAt' => (new \DateTimeImmutable())->modify('-1 minute'),
        ])->create()->_real();

        $mockClient = new MockHttpClient([
            new MockResponse('', ['http_code' => 304]),
        ]);

        $processMessage = $this->dispatchAndGetProcessMessage();

        $this->handleProcessMessage($processMessage, $mockClient);

        /** @var PublicationFetch|null $fetch */
        $fetch = $this->entityManager->getRepository(PublicationFetch::class)->findOneBy(['publication' => $publication]);
        $this->assertNotNull($fetch);
        $this->assertEquals(FetchStatusEnum::COMPLETED, $fetch->getStatus());
        $this->assertEquals(304, $fetch->getStatusCode());
        $this->assertEquals(0, $fetch->getNewItemsCount());
        $this->assertEquals(0, $fetch->getUpdatedItemsCount());
    }

    public function test_successful_feed_processing_creates_new_items(): void
    {
        $feedJson = json_encode([
            'version' => 'https://jsonfeed.org/version/1.1',
            'title'   => 'Integration Test Feed',
            'home_page_url' => 'https://example.com',
            'description' => 'Description for Integration Test Feed',
            'items' => [
                [
                    'id'           => 'item-1',
                    'url'          => 'https://example.com/1',
                    'title'        => 'Item 1',
                    'content_html' => '<p>Item Body</p>',
                ],
            ],
        ]);

        $headers = [
            'etag'           => ['"abc123"'],
            'last-modified'  => ['Wed, 21 Oct 2015 07:28:00 GMT'],
        ];

        $mockClient = new MockHttpClient([
            new MockResponse($feedJson, [
                'http_code'        => 200,
                'response_headers' => $headers,
            ]),
        ]);

        $collection = CollectionFactory::new()->create()->_real();
        $publication = PublicationFactory::new([
            'collection'  => $collection,
            'nextFetchAt' => (new \DateTimeImmutable())->modify('-1 minute'),
        ])->create()->_real();

        $processMessage = $this->dispatchAndGetProcessMessage();
        $this->handleProcessMessage($processMessage, $mockClient);

        /** @var PublicationFetch $fetch */
        $fetch = $this->entityManager->getRepository(PublicationFetch::class)->findOneBy(['publication' => $publication]);
        $this->assertEquals(FetchStatusEnum::COMPLETED, $fetch->getStatus());
        $this->assertEquals(200, $fetch->getStatusCode());
        $this->assertSame(1, $fetch->getNewItemsCount());
        $this->assertSame(0, $fetch->getUpdatedItemsCount());
        $this->assertNotNull($publication->getLastFetchedAt());
        $this->assertEquals('Integration Test Feed', $publication->getTitle());
        $this->entityManager->refresh($publication);
        $this->assertEquals('"abc123"', $publication->getConditionalGetEtag());
        $this->assertEquals('Wed, 21 Oct 2015 07:28:00 GMT', $publication->getConditionalGetLastModified());
        $this->assertEquals('Description for Integration Test Feed', $publication->getDescription());
    }

    public function test_unexpected_status_code_marks_fetch_failed(): void
    {
        $collection = CollectionFactory::new()->create()->_real();
        $publication = PublicationFactory::new([
            'collection'  => $collection,
            'nextFetchAt' => (new \DateTimeImmutable())->modify('-1 minute'),
        ])->create()->_real();

        $mockClient = new MockHttpClient([
            new MockResponse('Server error', ['http_code' => 500]),
        ]);

        $processMessage = $this->dispatchAndGetProcessMessage();
        $this->handleProcessMessage($processMessage, $mockClient);

        /** @var PublicationFetch $fetch */
        $fetch = $this->entityManager->getRepository(PublicationFetch::class)->findOneBy(['publication' => $publication]);
        $this->assertEquals(FetchStatusEnum::FAILED, $fetch->getStatus());
        $this->assertEquals(500, $fetch->getStatusCode());
        $this->assertNotNull($fetch->getError());
    }

    public function test_publication_not_found_is_ignored(): void
    {
        $mockClient = new MockHttpClient([
            new MockResponse('', ['http_code' => 200]),
        ]);

        $message = new ProcessFeedMessage(999999999);

        $this->handleProcessMessage($message, $mockClient);

        $fetchCount = $this->entityManager->getRepository(PublicationFetch::class)->count([]);
        $this->assertSame(0, $fetchCount, 'No PublicationFetch should have been created.');
    }

    public function test_transport_failure_marks_fetch_failed(): void
    {
        $collection = CollectionFactory::new()->create()->_real();
        $publication = PublicationFactory::new([
            'collection'  => $collection,
            'nextFetchAt' => (new \DateTimeImmutable())->modify('-1 minute'),
        ])->create()->_real();

        $mockClient = new MockHttpClient(function () {
            throw new TransportException('Network failure');
        });

        $processMessage = $this->dispatchAndGetProcessMessage();
        $this->handleProcessMessage($processMessage, $mockClient);

        /** @var PublicationFetch $fetch */
        $fetch = $this->entityManager->getRepository(PublicationFetch::class)->findOneBy(['publication' => $publication]);
        $this->assertEquals(FetchStatusEnum::FAILED, $fetch->getStatus());
        $this->assertEquals(0, $fetch->getStatusCode());
        $this->assertNotNull($fetch->getError());
    }

    public function test_conditional_headers_are_sent(): void
    {
        $collection = CollectionFactory::new()->create()->_real();
        $publication = PublicationFactory::new([
            'collection'  => $collection,
            'nextFetchAt' => (new \DateTimeImmutable())->modify('-1 hour'), 
        ])->create()->_real();

        $publication->setConditionalGetEtag('"etag123"');
        $publication->setConditionalGetLastModified('Wed, 22 Oct 2015 07:28:00 GMT');
        $this->entityManager->persist($publication);
        $this->entityManager->flush();

        $capturedHeaders = [];
        $mockClient = new MockHttpClient(function (string $method, string $url, array $options) use (&$capturedHeaders) {
            $capturedHeaders = $options['headers'] ?? [];
            return new MockResponse('', ['http_code' => 304]);
        });

        $processMessage = new ProcessFeedMessage($publication->getId());
        $this->handleProcessMessage($processMessage, $mockClient);

        $this->assertArrayHasKey('If-None-Match', $capturedHeaders);
        $this->assertArrayHasKey('If-Modified-Since', $capturedHeaders);
        $this->assertEquals('"etag123"', $capturedHeaders['If-None-Match']);
        $this->assertEquals('Wed, 22 Oct 2015 07:28:00 GMT', $capturedHeaders['If-Modified-Since']);
    }

    private function dispatchAndGetProcessMessage(): ProcessFeedMessage
    {
        $this->asyncTransport->reset();
        $this->bus->dispatch(new FetchMessage());

        $envelopes = $this->asyncTransport->get();
        $this->assertNotEmpty($envelopes, 'A ProcessFeedMessage should have been dispatched.');
        $message = $envelopes[0]->getMessage();
        $this->assertInstanceOf(ProcessFeedMessage::class, $message);

        return $message;
    }

    private function handleProcessMessage(ProcessFeedMessage $message, HttpClientInterface $httpClient): void
    {
        $fetchService = self::getContainer()->get(FetchService::class);
        $publicationRepository = self::getContainer()->get(PublicationRepository::class);
        $logger = self::getContainer()->get(LoggerInterface::class);

        $handler = new ProcessFeedHandler(
            $fetchService,
            $publicationRepository,
            $this->entityManager,
            $httpClient,
            $logger,
        );

        $handler->__invoke($message);
    }
} 