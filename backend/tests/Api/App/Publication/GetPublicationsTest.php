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

#[CoversClass(PublicationController::class)]
#[CoversClass(PublicationService::class)]
#[CoversClass(PublicationObject::class)]
class GetPublicationsTest extends WebTestCase
{
    public function test_get_publications_from_collection(): void
    {
        $collection = CollectionFactory::createOne(['hyvorUserId' => 1]);

        $publication1 = PublicationFactory::createOne(['collections' => [$collection]]);
        $publication2 = PublicationFactory::createOne(['collections' => [$collection]]);

        $this->client->request('GET', '/api/app/publications', ['collection_slug' => $collection->getSlug()]);
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Expected 200 OK');

        $content = $response->getContent();
        $this->assertIsString($content, 'Response content should be a string');
        $json = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($json, 'Decoded JSON should be an array');
        /** @var array<string, mixed> $json */
        $this->assertArrayHasKey('publications', $json);
        $this->assertIsArray($json['publications'], 'Publications should be an array');
        /** @var list<array<string, mixed>> $publications */
        $publications = $json['publications'];
        $this->assertCount(2, $publications);

        $slugs = array_map(fn(array $p) => $p['slug'], $publications);
        $this->assertContains($publication1->getSlug(), $slugs);
        $this->assertContains($publication2->getSlug(), $slugs);
    }

    public function test_get_publications_invalid_collection_returns_not_found(): void
    {
        $this->client->request('GET', '/api/app/publications', ['collection_slug' => 'non-existing']);
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function test_get_publications_missing_param_returns_bad_request(): void
    {
        $this->client->request('GET', '/api/app/publications');
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }
} 
