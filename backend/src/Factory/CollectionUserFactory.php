<?php

namespace App\Factory;

use App\Entity\CollectionUser;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<CollectionUser>
 */
final class CollectionUserFactory extends PersistentProxyObjectFactory
{
    public function __construct()
    {
    }

    public static function class(): string
    {
        return CollectionUser::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'hyvorUserId' => self::faker()->numberBetween(1, 1000),
            // 'collection' must be provided by caller
            'writeAccess' => true,
        ];
    }

    protected function initialize(): static
    {
        return $this;
    }
}


