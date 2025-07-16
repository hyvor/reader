<?php

namespace Api\App\Publication;

use App\Tests\WebTestCase;

class PublicationTest extends WebTestCase
{
    public function test_get_publications_with_valid_collection_slug(): void
    {
        $collection = $this->createCollectionForUserWithPublications();
        $response = $this->api('GET', '/publications?collection_slug=' . $collection->getSlug());
        $this->assertResponseIsSuccessful();
        $data = $this->getJsonResponseData();
        $this->assertArrayHasKey('publications', $data);
        $this->assertIsArray($data['publications']);
    }

    public function test_get_publications_with_invalid_collection_slug_returns_404(): void
    {
        $response = $this->api('GET', '/publications?collection_slug=invalid-slug');
        $this->assertResponseStatusCodeSame(404);
    }

    public function test_get_publications_with_missing_collection_slug_returns_400(): void
    {
        $response = $this->api('GET', '/publications');
        $this->assertResponseStatusCodeSame(400);
    }

    public function test_unauthenticated_user_gets_403_on_publications(): void
    {
        $collection = $this->createCollectionForUserWithPublications();
        $response = $this->api('GET', '/publications?collection_slug=' . $collection->getSlug(), [], false);
        $this->assertResponseStatusCodeSame(403);
    }

    public function test_get_publications_with_no_publications(): void
    {
        $collection = $this->createCollectionForUser([]);
        $response = $this->api('GET', '/publications?collection_slug=' . $collection->getSlug());
        $this->assertResponseIsSuccessful();
        $data = $this->getJsonResponseData();
        $this->assertIsArray($data['publications']);
        $this->assertCount(0, $data['publications']);
    }
} 