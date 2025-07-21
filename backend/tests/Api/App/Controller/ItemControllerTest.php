<?php

namespace App\Tests\Api\App\Controller;

use App\Api\App\Controller\ItemController;
use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\ResetDatabase;
use Zenstruck\Foundry\Test\Factories;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Factory\CollectionFactory;
use App\Factory\PublicationFactory;
use App\Factory\ItemFactory;

#[CoversClass(ItemController::class)]
class ItemControllerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;

    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function test_get_items_from_publication(): void
    {
        $collection = CollectionFactory::createOne(['hyvorUserId' => 1]);

        $publication = PublicationFactory::createOne([
            'collections' => [$collection],
        ]);

        ItemFactory::createMany(3, ['publication' => $publication]);

        $this->client->request('GET', '/api/app/items', ['publication_slug' => $publication->getSlug()]);
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Expected 200 OK');

        $json = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('items', $json);
        $this->assertCount(3, $json['items']);
    }

    public function test_get_items_from_collection(): void
    {
        $collection = CollectionFactory::createOne(['hyvorUserId' => 1])->_real();

        $publication1 = PublicationFactory::createOne()->_real();
        $publication2 = PublicationFactory::createOne()->_real();

        $collection->addPublication($publication1);
        $collection->addPublication($publication2);

        ItemFactory::createMany(2, ['publication' => $publication1]);
        ItemFactory::createMany(1, ['publication' => $publication2]);

        $this->em->clear();

        $this->client->request('GET', '/api/app/items', ['collection_slug' => $collection->getSlug()]);
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $json = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('items', $json);
        $this->assertCount(3, $json['items']);
    }

    public function test_get_items_missing_params_returns_bad_request(): void
    {
        $this->client->request('GET', '/api/app/items');
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }
} 