<?php

namespace App\Factory;

use App\Entity\Collection;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use App\InternalFake;

/**
 * @extends PersistentProxyObjectFactory<Collection>
 */
final class CollectionFactory extends PersistentProxyObjectFactory
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
        return Collection::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array|callable
    {
        return [
            'name' => self::faker()->words(2, true),
            'slug' => self::faker()->unique()->slug(),
            'hyvorUserId' => (new InternalFake())->user()->id,
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Collection $collection): void {})
        ;
    }
}
