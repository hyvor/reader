<?php

namespace App\Tests\Api\App;

use App\Api\App\Controller\InitController;
use App\Api\App\Object\CollectionObject;
use App\Service\Collection\CollectionService;
use App\Tests\Case\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(InitController::class)]
#[CoversClass(CollectionObject::class)]
#[CoversClass(CollectionService::class)]
class GetInitTest extends WebTestCase
{
    public function test_get_init_returns_collections(): void
    {
        $this->client->request('GET', '/api/app/init');

        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode(), 'Expected 200 OK response');

        $content = $response->getContent();
        $this->assertIsString($content, 'Response content should be a string');
        $json = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($json, 'Decoded JSON should be an array');
        /** @var array<string, mixed> $json */
        $this->assertArrayHasKey('collections', $json);
        $this->assertIsArray($json['collections'], 'Collections should be an array');
        /** @var list<array<string, mixed>> $collections */
        $collections = $json['collections'];
        $this->assertGreaterThanOrEqual(1, count($collections), 'At least one collection should be returned');

        /** @var array<string, mixed> $collection */
        $collection = $collections[0];
        $this->assertArrayHasKey('id', $collection);
        $this->assertArrayHasKey('name', $collection);
        $this->assertArrayHasKey('slug', $collection);
        $this->assertArrayHasKey('is_public', $collection);
        $this->assertArrayHasKey('is_owner', $collection);
        $this->assertTrue($collection['is_owner'], 'Current user should be owner of the default collection');
    }
} 
