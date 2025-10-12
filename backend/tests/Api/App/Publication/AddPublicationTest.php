<?php

namespace App\Tests\Api\App\Controller;

use App\Api\App\Controller\PublicationController;
use App\Api\App\Object\PublicationObject;
use App\Service\Publication\PublicationService;
use App\Tests\Case\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Factory\CollectionFactory;
use App\Factory\PublicationFactory;
use App\Service\Fetch\Message\ProcessFeedMessage;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(PublicationController::class)]
#[CoversClass(PublicationService::class)]
#[CoversClass(PublicationObject::class)]
class AddPublicationTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $mockClient = new MockHttpClient(function (string $method, string $url, array $options = []): MockResponse {
            if (str_contains($url, 'example.com/feed.xml')) {
                $body = (string) json_encode([
                    'version' => 'https://jsonfeed.org/version/1',
                    'title' => 'Test Publication',
                    'items' => [
                        ['id' => '1', 'url' => 'https://example.com/1', 'title' => 'Item 1']
                    ],
                ]);
                return new MockResponse($body, ['http_code' => 200, 'response_headers' => ['etag: W/"abc"']]);
            }

            if (str_contains($url, 'not-a-url')) {
                return new MockResponse('', ['http_code' => 404]);
            }

            $default = (string) json_encode([
                'version' => 'https://jsonfeed.org/version/1',
                'title' => 'Default Feed',
                'items' => [],
            ]);
            return new MockResponse($default, ['http_code' => 200]);
        });

        static::getContainer()->set(HttpClientInterface::class, $mockClient);
    }
    public function test_create_new_publication_and_queue_fetch(): void
    {
        $collection = CollectionFactory::createOne(['hyvorUserId' => 1])->_real();

        $payload = [
            'collection_slug' => $collection->getSlug(),
            'url' => 'https://example.com/feed.xml',
            'title' => 'Test Publication',
        ];

        $this->client->request(
            'POST',
            '/api/app/publications',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: (string) json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $json = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($json);
        $this->assertTrue($json['created']);
        $this->assertTrue($json['attached']);
        $this->assertArrayHasKey('publication', $json);
        $publicationArr = $json['publication'];
        $this->assertIsArray($publicationArr);
        $this->assertSame('https://example.com/feed.xml', $publicationArr['url']);
        $this->assertSame('Test Publication', $publicationArr['title']);

        /** @var \Zenstruck\Messenger\Test\Transport\TestTransport $transport */
        $transport = static::getContainer()->get('messenger.transport.async');
        $envelopes = iterator_to_array($transport->get());
        $this->assertCount(1, $envelopes);
        $this->assertInstanceOf(ProcessFeedMessage::class, $envelopes[0]->getMessage());
    }

    public function test_attach_existing_publication_and_queue_fetch(): void
    {
        $collection = CollectionFactory::createOne(['hyvorUserId' => 1])->_real();
        $publication = PublicationFactory::createOne(['url' => 'https://example.com/rss'])->_real();

        $payload = [
            'collection_slug' => $collection->getSlug(),
            'url' => $publication->getUrl(),
            'title' => 'Existing Publication',
        ];

        $this->client->request(
            'POST',
            '/api/app/publications',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: (string) json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $json = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($json);
        $this->assertFalse($json['created']);
        $this->assertTrue($json['attached']);

        /** @var \Zenstruck\Messenger\Test\Transport\TestTransport $transport */
        $transport = static::getContainer()->get('messenger.transport.async');
        $envelopes = iterator_to_array($transport->get());
        $this->assertCount(1, $envelopes);
        $this->assertInstanceOf(ProcessFeedMessage::class, $envelopes[0]->getMessage());
    }

    public function test_idempotent_attach_does_not_queue_fetch(): void
    {
        $collection = CollectionFactory::createOne(['hyvorUserId' => 1])->_real();
        $publication = PublicationFactory::createOne(['url' => 'https://example.com/idempotent', 'collections' => [$collection]])->_real();

        $payload = [
            'collection_slug' => $collection->getSlug(),
            'url' => $publication->getUrl(),
            'title' => 'Idempotent Publication',
        ];

        $this->client->request(
            'POST',
            '/api/app/publications',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: (string) json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $json = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($json);
        $this->assertFalse($json['created']);
        $this->assertFalse($json['attached']);

        /** @var \Zenstruck\Messenger\Test\Transport\TestTransport $transport */
        $transport = static::getContainer()->get('messenger.transport.async');
        $envelopes = iterator_to_array($transport->get());
        $this->assertCount(0, $envelopes);
    }

    public function test_invalid_url_returns_bad_request(): void
    {
        $collection = CollectionFactory::createOne(['hyvorUserId' => 1])->_real();

        $payload = [
            'collection_slug' => $collection->getSlug(),
            'url' => 'not-a-url',
            'title' => 'Invalid URL Test',
        ];

        $this->client->request(
            'POST',
            '/api/app/publications',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: (string) json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function test_collection_not_found(): void
    {
        $payload = [
            'collection_slug' => 'missing',
            'url' => 'https://example.com/feed',
            'title' => 'Collection Not Found',
        ];

        $this->client->request(
            'POST',
            '/api/app/publications',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: (string) json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function test_forbidden_without_write_access(): void
    {
        $collection = CollectionFactory::createOne(['hyvorUserId' => 2])->_real();

        $payload = [
            'collection_slug' => $collection->getSlug(),
            'url' => 'https://example.com/feed',
            'title' => 'Forbidden Test',
        ];

        $this->client->request(
            'POST',
            '/api/app/publications',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: (string) json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }
}


