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
class GetCollectionsTest extends WebTestCase
{
    public function test_get_collections_returns_only_current_users_collections(): void
    {
        $collectionService = $this->container->get(CollectionService::class);

        $collection1 = $collectionService->createCollection(1, 'User Collection 1', false);
        $collection2 = $collectionService->createCollection(1, 'User Collection 2', false);

        $otherCollection = $collectionService->createCollection(2, 'Other Users Collection', false);

        $this->client->request('GET', '/api/app/collections');
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Expected 200 OK');

        $data = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('collections', $data);
        $this->assertCount(2, $data['collections']);

        $slugs = array_map(fn(array $c) => $c['slug'], $data['collections']);
        $this->assertContains($collection1->getSlug(), $slugs);
        $this->assertContains($collection2->getSlug(), $slugs);
        $this->assertNotContains($otherCollection->getSlug(), $slugs);
    }

}
