<?php

namespace App\Tests\Api\App\Controller;

use App\Api\App\Controller\PublicationController;
use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\ResetDatabase;
use Zenstruck\Foundry\Test\Factories;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Factory\CollectionFactory;
use App\Factory\PublicationFactory;

#[CoversClass(PublicationController::class)]
class PublicationControllerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;

    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function test_get_publications_from_collection(): void
    {
        $collection = CollectionFactory::createOne(['hyvorUserId' => 1]);

        $publication1 = PublicationFactory::createOne(['collections' => [$collection]]);
        $publication2 = PublicationFactory::createOne(['collections' => [$collection]]);

        $this->client->request('GET', '/api/app/publications', ['collection_slug' => $collection->getSlug()]);
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Expected 200 OK');

        $json = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('publications', $json);
        $this->assertCount(2, $json['publications']);

        $slugs = array_map(fn(array $p) => $p['slug'], $json['publications']);
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