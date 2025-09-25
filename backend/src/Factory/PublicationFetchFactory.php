<?php

namespace App\Factory;

use App\Entity\PublicationFetch;
use App\Service\Fetch\FetchStatusEnum;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<PublicationFetch>
 */
final class PublicationFetchFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    public function __construct()
    {
    }

    public static function class(): string
    {
        return PublicationFetch::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'publication' => PublicationFactory::new(),
            'status' => self::faker()->randomElement(FetchStatusEnum::cases()),
            'statusCode' => self::faker()->optional(0.8)->numberBetween(200, 500),
            'error' => self::faker()->optional(0.2)->sentence(),
            'errorPrivate' => self::faker()->optional(0.1)->paragraph(),
            'newItemsCount' => self::faker()->numberBetween(0, 20),
            'updatedItemsCount' => self::faker()->numberBetween(0, 5),
            'latencyMs' => self::faker()->optional(0.9)->numberBetween(100, 5000),
            'createdAt' => self::faker()->dateTimeBetween('-1 month', 'now'),
            'updatedAt' => self::faker()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this;
    }
} 
