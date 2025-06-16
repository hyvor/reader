<?php

namespace App\Service;

use App\Entity\Publication;
use App\Entity\Item as EntityItem;
use App\Repository\ItemRepository;
use App\Service\Parser\Types\Feed;
use App\Service\Parser\Types\Item;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class FeedItemProcessor
{
    public function __construct(
        private ItemRepository $itemRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    /**
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
                'url' => $item->url // assuming URL is unique identifier
            ]);

            if ($existingItem) {
                $hasChanges = false;

                if ($existingItem->getTitle() !== $item->title) {
                    $existingItem->setTitle($item->title);
                    $hasChanges = true;
                }

                if ($existingItem->getSummary() !== $item->summary) {
                    $existingItem->setSummary($item->summary);
                    $hasChanges = true;
                }

                if ($item->content_html && $existingItem->getContentHtml() !== $item->content_html) {
                    $existingItem->setContentHtml($item->content_html);
                    $hasChanges = true;
                }

                if ($hasChanges) {
                    $existingItem->setUpdatedAt(new \DateTimeImmutable());
                    $updatedItemsCount++;
                    
                    $this->logger->debug('Updated existing item', [
                        'publication_id' => $publication->getId(),
                        'item_url' => $item->url,
                        'item_title' => $item->title
                    ]);
                }
            } else {
                $newItem = new EntityItem();
                $newItem->setPublication($publication);
                $newItem->setTitle($item->title);
                $newItem->setUrl($item->url);
                $newItem->setSummary($item->summary);
                
                if ($item->content_html) {
                    $newItem->setContentHtml($item->content_html);
                }
                
                if ($item->published_at) {
                    $newItem->setPublishedAt(\DateTimeImmutable::createFromInterface($item->published_at));
                }
                
                if ($item->updated_at) {
                    $newItem->setUpdatedAt(\DateTimeImmutable::createFromInterface($item->updated_at));
                } else {
                    $newItem->setUpdatedAt(new \DateTimeImmutable());
                }

                $authorNames = array_map(fn($author) => $author->name, $item->authors);
                $tagNames = array_map(fn($tag) => $tag->name, $item->tags);
                
                $newItem->setAuthors($authorNames);
                $newItem->setTags($tagNames);

                $this->entityManager->persist($newItem);
                $newItemsCount++;
                
                $this->logger->debug('Created new item', [
                    'publication_id' => $publication->getId(),
                    'item_url' => $item->url,
                    'item_title' => $item->title
                ]);
            }
        }

        return [
            'new_items' => $newItemsCount,
            'updated_items' => $updatedItemsCount
        ];
    }
} 