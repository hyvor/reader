<?php

namespace App\Tests\Api\App\Controller;

use App\Tests\WebTestCase;
use App\Service\Collection\CollectionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Factory\PublicationFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;
use App\Api\App\Controller\CollectionController;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CollectionController::class)]
class CollectionControllerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;

    private CollectionService $collectionService;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();

        $this->collectionService = $this->container->get(CollectionService::class);
        $this->em = $this->container->get(EntityManagerInterface::class);
    }

    public function test_get_collections_returns_only_current_users_collections(): void
    {
        $collection1 = $this->collectionService->createCollection(1, 'User Collection 1', false);
        $collection2 = $this->collectionService->createCollection(1, 'User Collection 2', false);

        $otherCollection = $this->collectionService->createCollection(2, 'Other Users Collection', false);

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

    public function test_get_single_collection_with_publications(): void
    {
        $collection = $this->collectionService->createCollection(1, 'Reading List', false);

        $publication1 = PublicationFactory::createOne(['collections' => [$collection]]);
        $publication2 = PublicationFactory::createOne(['collections' => [$collection]]);

        $this->client->request('GET', '/api/app/collections/' . $collection->getSlug());
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Expected 200 OK');

        $data = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('collection', $data);
        $this->assertArrayHasKey('publications', $data);

        $this->assertSame($collection->getSlug(), $data['collection']['slug']);
        $this->assertTrue($data['collection']['is_owner']);
        $this->assertCount(2, $data['publications']);

        $publicationSlugs = array_map(fn(array $p) => $p['slug'], $data['publications']);
        $this->assertContains($publication1->getSlug(), $publicationSlugs);
        $this->assertContains($publication2->getSlug(), $publicationSlugs);
    }
} 