<?php

namespace App\Tests\Service\Fetch\Handler;

use App\Entity\PublicationFetch;
use App\Factory\PublicationFactory;
use App\Service\Fetch\FetchService;
use App\Service\Fetch\Handler\ProcessFeedHandler;
use App\Service\Fetch\Message\ProcessFeedMessage;
use App\Service\Fetch\FetchStatusEnum;
use App\Tests\Case\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Zenstruck\Messenger\Test\Transport\TestTransport;

#[CoversClass(ProcessFeedHandler::class)]
#[CoversClass(FetchService::class)]
class ProcessFeedHandlerTest extends KernelTestCase
{
    private TestTransport $asyncTransport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asyncTransport = $this->transport('async');
    }

    public function test_process_feed_handler_with_non_existent_publication(): void
    {
        $this->asyncTransport->send(new ProcessFeedMessage(99999));
        $this->asyncTransport->process();

        $this->assertEquals(0, $this->em->getRepository(PublicationFetch::class)->count());
    }

    public function test_process_feed_handler_with_successful_response(): void
    {
        Clock::set(new MockClock('2025-06-24 00:00:00'));

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
                        'content_html' => '<p>Item 1 content</p>',
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

        $publication = PublicationFactory::createOne();

        $this->asyncTransport->send(new ProcessFeedMessage($publication->getId()));
        $this->asyncTransport->process();

        $fetch = $this->em->getRepository(PublicationFetch::class)->findOneBy(['publication' => $publication->getId()]);
        $this->assertNotNull($fetch);
        $this->assertEquals(FetchStatusEnum::COMPLETED, $fetch->getStatus());
        $this->assertEquals(200, $fetch->getStatusCode());
        
        $this->assertEquals('new-etag', $publication->getConditionalGetEtag());
        $this->assertEquals('Mon, 24 Jun 2025 00:00:00 GMT', $publication->getConditionalGetLastModified());

        $this->assertEquals(1, $fetch->getNewItemsCount());
        $this->assertEquals(0, $fetch->getUpdatedItemsCount());
        
        $this->assertNotNull($publication->getLastFetchedAt());
        $this->assertSame('2025-06-24 00:00:00', $publication->getLastFetchedAt()->format('Y-m-d H:i:s'));
    }

    public function test_process_feed_handler_with_not_modified_response(): void
    {
        $client = new MockHttpClient([
            new MockResponse('', [
                'http_code' => 304,
            ])
        ]);

        $this->container->set(HttpClientInterface::class, $client);

        $publication = PublicationFactory::createOne();

        $this->asyncTransport->send(new ProcessFeedMessage($publication->getId()));
        $this->asyncTransport->process();

        $fetch = $this->em->getRepository(PublicationFetch::class)->findOneBy(['publication' => $publication->getId()]);
        $this->assertNotNull($fetch);
        $this->assertEquals(FetchStatusEnum::COMPLETED, $fetch->getStatus());
        $this->assertEquals(304, $fetch->getStatusCode());
        $this->assertEquals(0, $fetch->getNewItemsCount());
        $this->assertEquals(0, $fetch->getUpdatedItemsCount());
    }

    public function test_process_feed_handler_with_error_status_code(): void
    {
        $publication = PublicationFactory::createOne();
        
        $client = new MockHttpClient([
            new MockResponse('', [
                'http_code' => 404,
            ])
        ]);

        $this->container->set(HttpClientInterface::class, $client);

        $this->asyncTransport->send(new ProcessFeedMessage($publication->getId()));
        $this->asyncTransport->process();

        $fetch = $this->em->getRepository(PublicationFetch::class)->findOneBy(['publication' => $publication->getId()]);
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

        $publication = PublicationFactory::createOne();

        $this->asyncTransport->send(new ProcessFeedMessage($publication->getId()));
        $this->asyncTransport->process();

        $fetch = $this->em->getRepository(PublicationFetch::class)->findOneBy(['publication' => $publication->getId()]);
        $this->assertNotNull($fetch);
        $this->assertEquals(FetchStatusEnum::FAILED, $fetch->getStatus());
        $this->assertEquals(0, $fetch->getStatusCode());
        $this->assertEquals('Connection timeout', $fetch->getError());
        $this->assertStringContainsString('Connection timeout', $fetch->getErrorPrivate());
    }

    public function test_process_feed_handler_with_conditional_get_headers(): void
    {
        $publication = PublicationFactory::createOne([
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
                        'id' => 'conditional-get-test-item',
                        'url' => 'https://example.com/items/conditional-get-test',
                        'title' => 'Conditional Get Test Item',
                        'summary' => 'Item for conditional get test',
                        'content_html' => '<p>Conditional get test content</p>',
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

        $fetch = $this->em->getRepository(PublicationFetch::class)->findOneBy(['publication' => $publication->getId()]);
        $this->assertNotNull($fetch);
        $this->assertEquals(FetchStatusEnum::COMPLETED, $fetch->getStatus());
        $this->assertEquals(200, $fetch->getStatusCode());
        $this->assertEquals('new-etag', $publication->getConditionalGetEtag());
        $this->assertEquals('Mon, 24 Jun 2025 00:00:00 GMT', $publication->getConditionalGetLastModified());
    }

    public function test_process_feed_handler_updates_feed_metadata(): void
    {
        $publication = PublicationFactory::createOne([
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

    public function test_is_fetching_flag(): void
    {
        $publication = PublicationFactory::createOne();
        
        $this->assertFalse($publication->getIsFetching());

        $client = new MockHttpClient([
            new JsonMockResponse([
                'version' => 'https://jsonfeed.org/version/1.1',
                'title' => 'Test Feed',
                'items' => []
            ])
        ]);

        $this->container->set(HttpClientInterface::class, $client);

        $this->asyncTransport->send(new ProcessFeedMessage($publication->getId()));
        $this->asyncTransport->process();

        $this->assertFalse($publication->getIsFetching());
    }
}
