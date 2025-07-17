<?php

namespace App\Service\Fetch;

use App\Repository\PublicationRepository;
use App\Repository\ItemRepository;
use App\Entity\Publication;
use App\Entity\Item;
use App\Service\Parser\Types\Feed;
use App\Service\Parser\Types\Item as ParsedItem;
use Doctrine\ORM\EntityManagerInterface;

class FetchService
{
    public function __construct(
        private PublicationRepository $publicationRepository,
        private ItemRepository $itemRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param \DateTimeImmutable $before
     * @return Publication[]
     */
    public function findDueForFetching(\DateTimeImmutable $before): array
    {
        return $this->publicationRepository
            ->createQueryBuilder('p')
            ->where('p.nextFetchAt <= :before')
            ->andWhere('p.isFetching = :isFetching')
            ->setParameter('before', $before)
            ->setParameter('isFetching', false)
            ->orderBy('p.nextFetchAt', 'ASC')
            ->setMaxResults(250)
            ->getQuery()
            ->getResult();
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
        $intervals = [];
        $previousDate = null;

        foreach ($feed->items as $item) {
            $existingItem = $this->itemRepository->findOneBy([
                'publication' => $publication->getId(),
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

            if ($item->published_at) {
                if ($previousDate !== null) {
                    $intervalMinutes = abs($item->published_at->getTimestamp() - $previousDate->getTimestamp()) / 60;
                    if ($intervalMinutes > 0) {
                        $intervals[] = $intervalMinutes;
                    }
                }
                $previousDate = $item->published_at;
            }
        }

        if ($newItemsCount > 0 && count($intervals) > 0) {
            $averageInterval = array_sum($intervals) / count($intervals);
            
            $minInterval = 15;
            $maxInterval = 24 * 60;
            $clampedInterval = max($minInterval, min($maxInterval, (int) round($averageInterval)));
            
            $publication->setInterval($clampedInterval);
        }

        return [
            'new_items' => $newItemsCount,
            'updated_items' => $updatedItemsCount,
        ];
    }

    /**
     * @param Publication $publication
     * @return void
     */
    public function updateNextFetchTime(Publication $publication): void
    {
        $interval = $publication->getInterval();
        $nextFetchAt = (new \DateTimeImmutable())->modify("+{$interval} minutes");
        
        $publication->setNextFetchAt($nextFetchAt);
    }

    /**
     * @param Item $existingItem
     * @param ParsedItem $parsedItem
     * @return bool
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
