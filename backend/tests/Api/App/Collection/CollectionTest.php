<?php

namespace Api\App\Collection;

use App\Tests\WebTestCase;

class CollectionTest extends WebTestCase
{
    public function test_authenticated_user_gets_collections(): void
    {
        $response = $this->api('GET', '/collections');
        $this->assertResponseIsSuccessful();
        $data = $this->getJsonResponseData();
        $this->assertArrayHasKey('collections', $data);
        $this->assertIsArray($data['collections']);
    }

    public function test_unauthenticated_user_gets_403_on_collections(): void
    {
        $response = $this->api('GET', '/collections', [], false);
        $this->assertResponseStatusCodeSame(403);
    }

    public function test_get_collection_by_valid_slug(): void
    {
        $collection = $this->createCollectionForUser();
        $response = $this->api('GET', '/collections/' . $collection->getSlug());
        $this->assertResponseIsSuccessful();
        $data = $this->getJsonResponseData();
        $this->assertArrayHasKey('collection', $data);
        $this->assertArrayHasKey('publications', $data);
        $this->assertIsArray($data['publications']);
    }

    public function test_get_collection_by_invalid_slug_returns_404(): void
    {
        $response = $this->api('GET', '/collections/invalid-slug');
        $this->assertResponseStatusCodeSame(404);
    }

    public function test_get_collection_by_slug_forbidden(): void
    {
        $collection = $this->createCollectionForAnotherUser();
        $response = $this->api('GET', '/collections/' . $collection->getSlug());
        $this->assertResponseStatusCodeSame(403);
    }

    public function test_get_collection_with_no_publications(): void
    {
        $collection = $this->createCollectionForUser([]);
        $response = $this->api('GET', '/collections/' . $collection->getSlug());
        $this->assertResponseIsSuccessful();
        $data = $this->getJsonResponseData();
        $this->assertIsArray($data['publications']);
        $this->assertCount(0, $data['publications']);
    }
} 