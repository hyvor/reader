<?php

namespace Api\App\Collection;

use App\Api\App\Controller\CollectionController;
use App\Api\App\Object\CollectionObject;
use App\Service\Collection\CollectionService;
use App\Factory\PublicationFactory;
use App\Tests\Case\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(CollectionController::class)]
#[CoversClass(CollectionObject::class)]
#[CoversClass(CollectionService::class)]
class GetCollectionBySlugTest extends WebTestCase
{
    public function test_get_single_collection_with_publications(): void
    {
        $collectionService = $this->container->get(CollectionService::class);
        $this->assertInstanceOf(CollectionService::class, $collectionService);

        $collection = $collectionService->createCollection(1, 'Reading List', false);

        $publication1 = PublicationFactory::createOne(['collections' => [$collection]]);
        $publication2 = PublicationFactory::createOne(['collections' => [$collection]]);

        $this->client->request('GET', '/api/app/collections/' . (string) $collection->getSlug());
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Expected 200 OK');

        $data = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('collection', $data);
        $this->assertArrayHasKey('publications', $data);
        $this->assertIsArray($data['publications']);

        $collectionArr = $data['collection'];
        $this->assertIsArray($collectionArr);
        $this->assertSame($collection->getSlug(), $collectionArr['slug']);
        $this->assertTrue($collectionArr['is_owner']);
        $this->assertCount(2, $data['publications']);

        $publications = $data['publications'];
        /** @var array<int, array{slug: string}> $publications */
        $publicationSlugs = array_map(static fn(array $p): string => (string) $p['slug'], $publications);
        $this->assertContains($publication1->getSlug(), $publicationSlugs);
        $this->assertContains($publication2->getSlug(), $publicationSlugs);
    }
}
