<?php

namespace Api\App\Item;

use App\Tests\WebTestCase;

class ItemTest extends WebTestCase
{
    public function test_get_items_with_valid_collection_slug(): void
    {
        $collection = $this->createCollectionForUserWithItems();
        $response = $this->api('GET', '/items?collection_slug=' . $collection->getSlug());
        $this->assertResponseIsSuccessful();
        $data = $this->getJsonResponseData();
        $this->assertArrayHasKey('items', $data);
        $this->assertIsArray($data['items']);
    }

    public function test_get_items_with_valid_publication_slug(): void
    {
        $publication = $this->createPublicationForUserWithItems();
        $response = $this->api('GET', '/items?publication_slug=' . $publication->getSlug());
        $this->assertResponseIsSuccessful();
        $data = $this->getJsonResponseData();
        $this->assertArrayHasKey('items', $data);
        $this->assertIsArray($data['items']);
    }

    public function test_get_items_with_missing_params_returns_400(): void
    {
        $response = $this->api('GET', '/items');
        $this->assertResponseStatusCodeSame(400);
    }

    public function test_get_items_with_invalid_slug_returns_404(): void
    {
        $response = $this->api('GET', '/items?collection_slug=invalid-slug');
        $this->assertResponseStatusCodeSame(404);
    }

    public function test_unauthenticated_user_gets_403_on_items(): void
    {
        $collection = $this->createCollectionForUserWithItems();
        $response = $this->api('GET', '/items?collection_slug=' . $collection->getSlug(), [], false);
        $this->assertResponseStatusCodeSame(403);
    }

    public function test_get_items_with_no_items(): void
    {
        $collection = $this->createCollectionForUser([]);
        $response = $this->api('GET', '/items?collection_slug=' . $collection->getSlug());
        $this->assertResponseIsSuccessful();
        $data = $this->getJsonResponseData();
        $this->assertIsArray($data['items']);
        $this->assertCount(0, $data['items']);
    }

    public function test_pagination_limits(): void
    {
        $collection = $this->createCollectionForUserWithItems(150);
        $response = $this->api('GET', '/items?collection_slug=' . $collection->getSlug() . '&limit=200');
        $this->assertResponseIsSuccessful();
        $data = $this->getJsonResponseData();
        $this->assertLessThanOrEqual(100, count($data['items']));

        $response = $this->api('GET', '/items?collection_slug=' . $collection->getSlug() . '&limit=0');
        $this->assertResponseIsSuccessful();
        $data = $this->getJsonResponseData();
        $this->assertGreaterThanOrEqual(1, count($data['items']));
    }
} 