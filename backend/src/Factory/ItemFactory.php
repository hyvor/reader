<?php

namespace App\Factory;

use App\Entity\Item;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Item>
 */
final class ItemFactory extends PersistentProxyObjectFactory
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
        return Item::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array|callable
    {
        return [
            'guid' => self::faker()->uuid(),
            'url' => self::faker()->url(),
            'title' => self::faker()->sentence(6),
            'slug' => self::faker()->unique()->slug(),
            'content_html' => '<p>' . self::faker()->paragraphs(self::faker()->numberBetween(2, 5), true) . '</p>',
            'content_text' => self::faker()->paragraphs(self::faker()->numberBetween(2, 5), true),
            'summary' => self::faker()->sentence(10),
            'image' => 'https://picsum.photos/800/400?random=' . self::faker()->numberBetween(1, 1000),
            'published_at' => self::faker()->dateTimeBetween('-30 days', '-1 day'),
            'authors' => [self::faker()->name()],
            'tags' => self::faker()->words(self::faker()->numberBetween(2, 5)),
            'language' => 'en',
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Item $item): void {})
        ;
    }
}
