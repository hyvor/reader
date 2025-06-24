<?php

namespace App\Tests\Service\Fetch\Handler;

use App\Entity\Collection;
use App\Factory\CollectionFactory;
use App\Factory\PublicationFactory;
use App\Repository\PublicationFetchRepository;
use App\Service\Fetch\Message\ProcessFeedMessage;
use App\Service\Fetch\FetchStatusEnum;
use App\Tests\Case\KernelTestCase;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Messenger\Test\Transport\TestTransport;

class ProcessFeedHandlerTest extends KernelTestCase
{
    use Factories;

    private Collection $collection;
    private TestTransport $asyncTransport;
    private PublicationFactory $publicationFactory;
    private PublicationFetchRepository $publicationFetchRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->asyncTransport = $this->transport('async');

        $this->em->createQuery('DELETE FROM App\Entity\PublicationFetch')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\Item')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\Publication')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\Collection')->execute();

        /** @var CollectionFactory $collectionFactory */
        $collectionFactory = $this->container->get(CollectionFactory::class);
        $this->collection = $collectionFactory->createOne();

        /** @var PublicationFactory $publicationFactory */
        $this->publicationFactory = $this->container->get(PublicationFactory::class);

        /** @var PublicationFetchRepository $publicationFetchRepository */
        $this->publicationFetchRepository = $this->container->get(PublicationFetchRepository::class);
    }

    public function test_process_feed_handler_with_non_existent_publication(): void
    {
        $this->asyncTransport->send(new ProcessFeedMessage(99999));
        $this->asyncTransport->process();

        $this->assertEquals(0, $this->publicationFetchRepository->count());
    }

    public function test_process_feed_handler_with_successful_response(): void
    {
        $client = new MockHttpClient([
            new JsonMockResponse([
                'version' => 'https://jsonfeed.org/version/1.1',
                'title' => 'Title',
                'description' => 'Description',
                'home_page_url' => 'https://example.com',
                'items' => [
                    [
                        'id' => '1',
                        'url' => 'https://example.com/items/1',
                        'title' => 'Item 1',
                        'summary' => 'Item 1 description',
                        'date_published' => '2016-11-23T07:28:00Z'
                    ]
                ]
            ], [
                'response_headers' => [
                    'content-type' => 'application/json',
                    'etag' => 'new-etag',
                    'last-modified' => 'Mon, 24 Jun 2025 00:00:00 GMT'
                ]
            ])
        ]);

        $this->container->set(HttpClientInterface::class, $client);

        $publication = $this->publicationFactory->createOne([
            'collection' => $this->collection,
        ]);

        $beforeProcessing = new \DateTimeImmutable();
        $this->asyncTransport->send(new ProcessFeedMessage($publication->getId()));
        $this->asyncTransport->process();
        $afterProcessing = new \DateTimeImmutable();

        $fetch = $this->publicationFetchRepository->findOneBy(['publication' => $publication->getId()]);
        $this->assertNotNull($fetch);
        $this->assertEquals(FetchStatusEnum::COMPLETED, $fetch->getStatus());
        $this->assertEquals(200, $fetch->getStatusCode());
        
        $this->assertEquals('new-etag', $publication->getConditionalGetEtag());
        $this->assertEquals('Mon, 24 Jun 2025 00:00:00 GMT', $publication->getConditionalGetLastModified());
        
        $this->assertNotNull($publication->getLastFetchedAt());
        $this->assertGreaterThanOrEqual($beforeProcessing->getTimestamp(), $publication->getLastFetchedAt()->getTimestamp());
        $this->assertLessThanOrEqual($afterProcessing->getTimestamp(), $publication->getLastFetchedAt()->getTimestamp());
    }

    public function test_process_feed_handler_with_not_modified_response(): void
    {
        $client = new MockHttpClient([
            new MockResponse('', [
            'http_code' => 304,
            ])
        ]);

        $this->container->set(HttpClientInterface::class, $client);

        $publication = $this->publicationFactory->createOne([
            'collection' => $this->collection,
        ]);

        $this->asyncTransport->send(new ProcessFeedMessage($publication->getId()));
        $this->asyncTransport->process();

        $fetch = $this->publicationFetchRepository->findOneBy(['publication' => $publication->getId()]);
        $this->assertNotNull($fetch);
        $this->assertEquals(FetchStatusEnum::COMPLETED, $fetch->getStatus());
        $this->assertEquals(304, $fetch->getStatusCode());
        $this->assertEquals(0, $fetch->getNewItemsCount());
        $this->assertEquals(0, $fetch->getUpdatedItemsCount());
    }

    public function test_process_feed_handler_with_error_status_code(): void
    {
        $publication = $this->publicationFactory->createOne([
            'collection' => $this->collection,
        ]);
        
        $client = new MockHttpClient([
            new MockResponse('', [
                'http_code' => 404,
            ])
        ]);

        $this->container->set(HttpClientInterface::class, $client);

        $this->asyncTransport->send(new ProcessFeedMessage($publication->getId()));
        $this->asyncTransport->process();

        $fetch = $this->publicationFetchRepository->findOneBy(['publication' => $publication->getId()]);
        $this->assertNotNull($fetch);
        $this->assertEquals(FetchStatusEnum::FAILED, $fetch->getStatus());
        $this->assertEquals(404, $fetch->getStatusCode());
        $this->assertEquals('Unexpected HTTP status code: 404', $fetch->getError());
    }

    public function test_process_feed_handler_with_transport_exception(): void
    {
        $client = new MockHttpClient();
        $client->setResponseFactory(function () {
            throw new TransportException('Connection timeout');
        });

        $this->container->set(HttpClientInterface::class, $client);

        $publication = $this->publicationFactory->createOne([
            'collection' => $this->collection,
        ]);

        $this->asyncTransport->send(new ProcessFeedMessage($publication->getId()));
        $this->asyncTransport->process();

        $fetch = $this->publicationFetchRepository->findOneBy(['publication' => $publication->getId()]);
        $this->assertNotNull($fetch);
        $this->assertEquals(FetchStatusEnum::FAILED, $fetch->getStatus());
        $this->assertEquals(0, $fetch->getStatusCode());
        $this->assertEquals('Connection timeout', $fetch->getError());
        $this->assertStringContainsString('Connection timeout', $fetch->getErrorPrivate());
    }

    public function test_process_feed_handler_with_conditional_get_headers(): void
    {
        $publication = $this->publicationFactory->createOne([
            'collection' => $this->collection,
            'conditionalGetEtag' => 'old-etag',
            'conditionalGetLastModified' => 'Mon, 23 Jun 2025 00:00:00 GMT',
        ]);
        
        $client = new MockHttpClient([
            new JsonMockResponse([
                'version' => 'https://jsonfeed.org/version/1.1',
                'title' => 'Title',
                'description' => 'Description',
                'home_page_url' => 'https://example.com',
                'items' => [
                    [
                        'id' => '1',
                        'url' => 'https://example.com/items/1',
                        'title' => 'Item 1',
                        'summary' => 'Item 1 description',
                        'date_published' => '2016-11-23T07:28:00Z'
                    ]
                ]
            ], [
                'response_headers' => [
                    'content-type' => 'application/json',
                    'etag' => 'new-etag',
                    'last-modified' => 'Mon, 24 Jun 2025 00:00:00 GMT',
                ]
            ])
        ]);

        $this->container->set(HttpClientInterface::class, $client);

        $this->asyncTransport->send(new ProcessFeedMessage($publication->getId()));
        $this->asyncTransport->process();

        $fetch = $this->publicationFetchRepository->findOneBy(['publication' => $publication->getId()]);
        $this->assertNotNull($fetch);
        $this->assertEquals(FetchStatusEnum::COMPLETED, $fetch->getStatus());
        $this->assertEquals(200, $fetch->getStatusCode());
        $this->assertEquals('new-etag', $publication->getConditionalGetEtag());
        $this->assertEquals('Mon, 24 Jun 2025 00:00:00 GMT', $publication->getConditionalGetLastModified());
    }

    public function test_process_feed_handler_updates_feed_metadata(): void
    {
        $publication = $this->publicationFactory->createOne([
            'collection' => $this->collection,
            'title' => 'Old Title',
            'description' => 'Old Description',
            'conditionalGetEtag' => 'old-etag',
            'conditionalGetLastModified' => 'Mon, 23 Jun 2025 00:00:00 GMT',
        ]);
        
        $client = new MockHttpClient([
            new JsonMockResponse([
                'version' => 'https://jsonfeed.org/version/1.1',
                'title' => 'Updated Feed Title',
                'description' => 'Updated feed description',
                'home_page_url' => 'https://example.com',
                'items' => []
            ], [
                'response_headers' => [
                    'content-type' => 'application/json',
                    'etag' => 'new-etag',
                    'last-modified' => 'Mon, 24 Jun 2025 00:00:00 GMT'
                ]
            ])
        ]);

        $this->container->set(HttpClientInterface::class, $client);

        $this->asyncTransport->send(new ProcessFeedMessage($publication->getId()));
        $this->asyncTransport->process();

        $this->assertEquals('Updated Feed Title', $publication->getTitle());
        $this->assertEquals('Updated feed description', $publication->getDescription());
    }
}
