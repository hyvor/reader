<?php

namespace App\Service\Fetch;

use App\Repository\PublicationRepository;
use App\Repository\ItemRepository;
use App\Entity\Publication;
use App\Entity\Item;
use App\Service\Parser\Types\Feed;
use App\Service\Parser\Types\Item as ParsedItem;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class FetchService
{
    public function __construct(
        private PublicationRepository $publicationRepository,
        private ItemRepository $itemRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Get publications that are due for fetching.
     *
     * @param \DateTimeImmutable $before Timestamp to compare against publications' nextFetchAt.
     * @return Publication[] Returns an array of Publication entities that should be fetched.
     */
    public function findDueForFetching(\DateTimeImmutable $before): array
    {
        return $this->publicationRepository
            ->createQueryBuilder('p')
            ->where('p.nextFetchAt <= :before')
            ->setParameter('before', $before)
            ->orderBy('p.nextFetchAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Process items in a parsed feed and persist new/updated items for the given publication.
     *
     * @param Publication $publication
     * @param Feed $feed
     * @return array{new_items: int, updated_items: int}
     */
    public function processItems(Publication $publication, Feed $feed): array
    {
        $newItemsCount = 0;
        $updatedItemsCount = 0;

        foreach ($feed->items as $item) {
            $existingItem = $this->itemRepository->findOneBy([
                'publication' => $publication,
                'guid' => $item->id, 
            ]);

            if ($existingItem) {
                if ($this->updateExistingItem($existingItem, $item)) {
                    $updatedItemsCount++;
                }
            } else {
                $this->addNewItem($publication, $item);
                $newItemsCount++;
            }
        }

        return [
            'new_items' => $newItemsCount,
            'updated_items' => $updatedItemsCount,
        ];
    }

    /**
     * Update an existing item with new data from the parsed feed
     * 
     * @param Item $existingItem
     * @param ParsedItem $parsedItem
     * @return bool True if the item was updated, false if no changes were made
     */
    private function updateExistingItem(Item $existingItem, ParsedItem $parsedItem): bool
    {
        $hasChanges = false;

        if ($existingItem->getTitle() !== $parsedItem->title) {
            $existingItem->setTitle($parsedItem->title);
            $hasChanges = true;
        }

        if ($existingItem->getSummary() !== $parsedItem->summary) {
            $existingItem->setSummary($parsedItem->summary);
            $hasChanges = true;
        }

        if ($parsedItem->content_html && $existingItem->getContentHtml() !== $parsedItem->content_html) {
            $existingItem->setContentHtml($parsedItem->content_html);
            $hasChanges = true;
        }

        if ($parsedItem->content_text && $existingItem->getContentText() !== $parsedItem->content_text) {
            $existingItem->setContentText($parsedItem->content_text);
            $hasChanges = true;
        }

        if ($parsedItem->image && $existingItem->getImage() !== $parsedItem->image) {
            $existingItem->setImage($parsedItem->image);
            $hasChanges = true;
        }

        if ($parsedItem->language && $existingItem->getLanguage() !== $parsedItem->language) {
            $existingItem->setLanguage($parsedItem->language);
            $hasChanges = true;
        }

        if ($existingItem->getUrl() !== $parsedItem->url) {
            $existingItem->setUrl($parsedItem->url);
            $hasChanges = true;
        }

        if ($existingItem->getPublishedAt()?->getTimestamp() !== $parsedItem->published_at?->getTimestamp()) {
            $existingItem->setPublishedAt($parsedItem->published_at);
            $hasChanges = true;
        }

        $newAuthorNames = array_map(fn($author) => $author->name, $parsedItem->authors);
        $newTagNames = array_map(fn($tag) => $tag->name, $parsedItem->tags);
        
        if ($existingItem->getAuthors() !== $newAuthorNames) {
            $existingItem->setAuthors($newAuthorNames);
            $hasChanges = true;
        }

        if ($existingItem->getTags() !== $newTagNames) {
            $existingItem->setTags($newTagNames);
            $hasChanges = true;
        }

        if ($hasChanges) {
            $existingItem->setUpdatedAt(new \DateTimeImmutable());
        }

        return $hasChanges;
    }

    /**
     * Add a new item from the parsed feed
     * 
     * @param Publication $publication
     * @param ParsedItem $parsedItem
     * @return void
     */
    private function addNewItem(Publication $publication, ParsedItem $parsedItem): void
    {
        $newItem = new Item();
        $newItem->setPublication($publication);
        $newItem->setGuid($parsedItem->id);
        $newItem->setTitle($parsedItem->title);
        $newItem->setUrl($parsedItem->url);
        $newItem->setSummary($parsedItem->summary);

        if ($parsedItem->content_html) {
            $newItem->setContentHtml($parsedItem->content_html);
        }

        if ($parsedItem->content_text) {
            $newItem->setContentText($parsedItem->content_text);
        }

        if ($parsedItem->image) {
            $newItem->setImage($parsedItem->image);
        }

        if ($parsedItem->language) {
            $newItem->setLanguage($parsedItem->language);
        }

        if ($parsedItem->published_at) {
            $newItem->setPublishedAt($parsedItem->published_at);
        }

        if ($parsedItem->updated_at) {
            $newItem->setUpdatedAt($parsedItem->updated_at);
        } else {
            $newItem->setUpdatedAt(new \DateTimeImmutable());
        }

        $authorNames = array_map(fn($author) => $author->name, $parsedItem->authors);
        $tagNames = array_map(fn($tag) => $tag->name, $parsedItem->tags);

        $newItem->setAuthors($authorNames);
        $newItem->setTags($tagNames);

        $this->entityManager->persist($newItem);
    }
} 
