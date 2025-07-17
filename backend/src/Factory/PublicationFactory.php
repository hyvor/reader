<?php

namespace App\Factory;

use App\Entity\Collection;
use App\Entity\Publication;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Publication>
 */
final class PublicationFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    public static function class(): string
    {
        return Publication::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array|callable
    {
        return [
            'url' => self::faker()->url(),
            'title' => self::faker()->words(3, true),
            'slug' => self::faker()->unique()->slug(),
            'description' => self::faker()->sentence(),
            'interval' => self::faker()->numberBetween(30, 180),
            'createdAt' => self::faker()->dateTimeBetween('-6 months', '-1 month'),
            'updatedAt' => self::faker()->dateTimeBetween('-1 month', 'now'),
            'lastFetchedAt' => self::faker()->dateTimeBetween('-1 week', 'now'),
            'nextFetchAt' => self::faker()->dateTimeBetween('now', '+2 hours'),
            'subscribers' => self::faker()->numberBetween(100, 5000),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Publication $publication): void {})
        ;
    }
}
