<?php

namespace App\Tests;

use Hyvor\Internal\Auth\AuthFake;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\DependencyInjection\Container;
use App\Factory\CollectionFactory;
use App\Factory\PublicationFactory;
use App\Factory\ItemFactory;

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
    }

    protected function api(string $method, string $url, array $data = [], bool $authenticated = true)
    {
        if ($authenticated) {
            $this->client->setServerParameter('HTTP_COOKIE', 'AUTH_SESSION=test-session');
        } else {
            $this->client->setServerParameter('HTTP_COOKIE', '');
        }
        $this->client->request($method, $url, $data);
        return $this->client->getResponse();
    }

    protected function getJsonResponseData()
    {
        $content = $this->client->getResponse()->getContent();
        return json_decode($content, true);
    }

    protected function createCollectionForUser(array $publications = null)
    {
        $collection = CollectionFactory::new()->create();
        if ($publications !== null) {
            foreach ($publications as $pubData) {
                PublicationFactory::new(array_merge(['collection' => $collection], $pubData))->create();
            }
        }
        return $collection->object();
    }

    protected function createCollectionForAnotherUser()
    {
        $collection = CollectionFactory::new(['hyvorUserId' => 9999])->create();
        return $collection->object();
    }

    protected function createCollectionForUserWithPublications($count = 2)
    {
        $collection = CollectionFactory::new()->create();
        PublicationFactory::new(['collection' => $collection])->many($count)->create();
        return $collection->object();
    }

    protected function createCollectionForUserWithItems($itemCount = 10)
    {
        $collection = CollectionFactory::new()->create();
        $publication = PublicationFactory::new(['collection' => $collection])->create();
        ItemFactory::new(['publication' => $publication])->many($itemCount)->create();
        return $collection->object();
    }

    protected function createPublicationForUserWithItems($itemCount = 10)
    {
        $collection = CollectionFactory::new()->create();
        $publication = PublicationFactory::new(['collection' => $collection])->create();
        ItemFactory::new(['publication' => $publication])->many($itemCount)->create();
        return $publication->object();
    }
} 