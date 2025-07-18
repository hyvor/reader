<?php

namespace App\Tests;

use Hyvor\Internal\Auth\AuthFake;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\DependencyInjection\Container;
use App\Factory\CollectionFactory;
use App\Factory\PublicationFactory;
use App\Factory\ItemFactory;
use Symfony\Component\BrowserKit\Cookie;

class WebTestCase extends BaseWebTestCase
{

    protected KernelBrowser $client;
    protected Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->container = static::getContainer();

        AuthFake::enableForSymfony($this->container, ['id' => 1]);
        $this->client->getCookieJar()->set(new Cookie('authsess', 'test'));
    }
} 