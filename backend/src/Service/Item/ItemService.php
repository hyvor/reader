<?php

namespace App\Service\Item;

use App\Entity\Item;
use App\Entity\Publication;
use App\Entity\Collection;
use App\Api\App\Object\ItemObject;
use App\Service\Publication\PublicationService;
use App\Service\Collection\CollectionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

class ItemService
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * @return ItemObject[]
     */
    public function getItemsFromPublication(Publication $publication, int $limit = 50, int $offset = 0): array
    {
        $items = $publication->getItems()->slice($offset, $limit);
        return array_map(fn(Item $item) => new ItemObject($item), $items);
    }

    /**
     * @return ItemObject[]
     */
    public function getItemsFromCollection(Collection $collection, int $limit = 50, int $offset = 0): array
    {
        // TODO: use a query with sorting
        $allItems = [];
        foreach ($collection->getPublications() as $publication) {
            foreach ($publication->getItems() as $item) {
                $allItems[] = new ItemObject($item);
            }
        }

        usort($allItems, function ($a, $b) {
            return ($b->published_at ?? 0) <=> ($a->published_at ?? 0);
        });

        return array_slice($allItems, $offset, $limit);
    }

    public function createItem(Publication $publication, string $title, string $url, string $guid): Item
    {
        $item = new Item();
        $item->setTitle($title);
        $item->setUrl($url);
        $item->setPublication($publication);
        $item->setGuid($guid);
        $item->setSlug($this->generateUniqueSlug($title));

        $this->em->persist($item);
        $this->em->flush();

        return $item;
    }

    private function generateUniqueSlug(string $text): string
    {
        $slugger = new AsciiSlugger();
        $baseSlug = $slugger->slug($text)->lower()->toString();
        $slug = $baseSlug;
        $counter = 1;

        while ($this->em->getRepository(Item::class)->findOneBy(['slug' => $slug])) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
} 