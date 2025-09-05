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

#[CoversClass(PublicationController::class)]
#[CoversClass(PublicationService::class)]
#[CoversClass(PublicationObject::class)]
class AddPublicationTest extends WebTestCase
{
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
            content: json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $json = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertTrue($json['created']);
        $this->assertTrue($json['attached']);
        $this->assertArrayHasKey('publication', $json);
        $this->assertSame('https://example.com/feed.xml', $json['publication']['url']);
        $this->assertSame('Test Publication', $json['publication']['title']);

        $transport = static::getContainer()->get('messenger.transport.async');
        $envelopes = $transport->get();
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
            content: json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $json = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertFalse($json['created']);
        $this->assertTrue($json['attached']);

        $transport = static::getContainer()->get('messenger.transport.async');
        $envelopes = $transport->get();
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
            content: json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $json = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertFalse($json['created']);
        $this->assertFalse($json['attached']);

        $transport = static::getContainer()->get('messenger.transport.async');
        $envelopes = $transport->get();
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
            content: json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
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
            content: json_encode($payload)
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
            content: json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }
}


