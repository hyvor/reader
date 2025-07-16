<?php

namespace Api\App\Init;

use App\Tests\WebTestCase;

class InitTest extends WebTestCase
{
    public function test_authenticated_user_receives_collections(): void
    {
        $response = $this->api('GET', '/init');
        $this->assertResponseIsSuccessful();
        $data = $this->getJsonResponseData();
        $this->assertArrayHasKey('collections', $data);
        $this->assertIsArray($data['collections']);
    }

    public function test_unauthenticated_user_gets_403(): void
    {
        $response = $this->api('GET', '/init', [], false);
        $this->assertResponseStatusCodeSame(403);
    }
} 