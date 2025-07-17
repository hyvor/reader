<?php

namespace App\Tests\Case;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Container;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class KernelTestCase extends \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase
{
    use InteractsWithMessenger;
    use Factories;
    use ResetDatabase;

    protected Container $container;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->container = static::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $this->container->get(EntityManagerInterface::class);
        $this->em = $em;
    }
}
