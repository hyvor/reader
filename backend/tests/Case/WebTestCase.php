<?php

namespace App\Tests\Case;

// use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Auth\AuthFake;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\BrowserKit\Cookie;
use Zenstruck\Foundry\Test\Factories;

class WebTestCase extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{
    use Factories;

    protected KernelBrowser $client;
    protected Container $container;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();
        // StaticDriver::setKeepStaticConnections(true);

        $this->client = static::createClient();
        $this->container = static::getContainer();

        $this->em = $this->container->get(EntityManagerInterface::class);

        AuthFake::enableForSymfony($this->container, ['id' => 1]);
        $this->client->getCookieJar()->set(new Cookie('authsess', 'test'));
    }
}
