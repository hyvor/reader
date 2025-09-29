<?php

namespace Api\App\Collection;

use App\Api\App\Controller\CollectionController;
use App\Api\App\Object\CollectionObject;
use App\Service\Collection\CollectionService;
use App\Tests\Case\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(CollectionController::class)]
#[CoversClass(CollectionObject::class)]
#[CoversClass(CollectionService::class)]
class CreateCollectionsTest extends WebTestCase
{
    public function test_create_private_collection(): void
    {
        $this->client->request('POST', '/api/app/collections', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => 'My Private Collection',
            'is_public' => false,
        ]));

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Expected 200 OK');

        $data = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('collection', $data);

        $collection = $data['collection'];
        $this->assertSame('My Private Collection', $collection['name']);
        $this->assertFalse($collection['is_public']);
        $this->assertTrue($collection['is_owner']);
        $this->assertArrayHasKey('slug', $collection);
        $this->assertNotEmpty($collection['slug']);
    }

    public function test_create_public_collection(): void
    {
        $this->client->request('POST', '/api/app/collections', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => 'My Public Collection',
            'is_public' => true,
        ]));

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Expected 200 OK');

        $data = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('collection', $data);

        $collection = $data['collection'];
        $this->assertSame('My Public Collection', $collection['name']);
        $this->assertTrue($collection['is_public']);
        $this->assertTrue($collection['is_owner']);
        $this->assertArrayHasKey('slug', $collection);
        $this->assertNotEmpty($collection['slug']);
    }

    public function test_create_collection_with_trimmed_name(): void
    {
        $this->client->request('POST', '/api/app/collections', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => '  Trimmed Collection Name  ',
            'is_public' => false,
        ]));

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Expected 200 OK');

        $data = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('collection', $data);

        $collection = $data['collection'];
        $this->assertSame('Trimmed Collection Name', $collection['name']);
    }

    public function test_create_collection_requires_name(): void
    {
        $this->client->request('POST', '/api/app/collections', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'is_public' => false,
        ]));

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode(), 'Expected 422 Unprocessable Entity');
    }

    public function test_create_collection_requires_valid_json(): void
    {
        $this->client->request('POST', '/api/app/collections', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], 'invalid json');

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), 'Expected 400 Bad Request');
    }
}


