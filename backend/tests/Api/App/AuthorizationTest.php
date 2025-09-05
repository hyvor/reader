<?php

namespace App\Tests\Api\App;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Hyvor\Internal\Auth\AuthFake;

class AuthorizationTest extends WebTestCase
{
    public function test_unauthenticated_requests_return_401(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/app/main');

        $this->assertSame(401, $client->getResponse()->getStatusCode());
    }

    public function test_authenticated_requests_pass_through(): void
    {
        $client = static::createClient();

        AuthFake::enableForSymfony(self::getContainer(), ['id' => 1]);

        $client->request('GET', '/api/app/main');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }
}


