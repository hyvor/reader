<?php

namespace App\Tests\Service\Fetch;

use App\Entity\Item;
use App\Entity\Publication;
use App\Factory\ItemFactory;
use App\Factory\PublicationFactory;
use App\Service\Fetch\FetchService;
use App\Service\Parser\Types\Feed;
use App\Service\Parser\Types\FeedType;
use App\Service\Parser\Types\Item as ParsedItem;
use App\Service\Parser\Types\Author;
use App\Service\Parser\Types\Tag;
use App\Tests\Case\KernelTestCase;

class FetchServiceTest extends KernelTestCase
{
    private FetchService $fetchService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fetchService = $this->container->get(FetchService::class);
    }

    public function test_findDueForFetching_finds_due_publications(): void
    {
        $duePublication = PublicationFactory::createOne([
            'nextFetchAt' => new \DateTimeImmutable('-30 minutes'),
        ]);
        
        PublicationFactory::createOne([
            'nextFetchAt' => new \DateTimeImmutable('+30 minutes'),
        ]);

        $before = new \DateTimeImmutable();
        $result = $this->fetchService->findDueForFetching($before);

        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf(Publication::class, $result);
        $this->assertEquals($duePublication->getId(), $result[0]->getId());
    }

    public function test_processItems_adds_new_items_with_correct_values(): void
    {
        $publication = PublicationFactory::createOne()->_real();
        $this->em->flush();

        $publishedAt = new \DateTimeImmutable('-2 hours');
        
        $feed = $this->createTestFeed([
            $this->createTestParsedItem(
                id: 'new-item',
                title: 'Test Item Title',
                url: 'https://example.com/test-item',
                summary: 'Test summary',
                contentHtml: '<p>Test HTML content</p>',
                contentText: 'Test text content',
                image: 'https://example.com/image.jpg',
                language: 'en',
                publishedAt: $publishedAt,
                authors: [new Author('Test Author', null, null)],
                tags: [new Tag('test-tag')]
            ),
        ]);

        $result = $this->fetchService->processItems($publication, $feed);
        
        $this->assertEquals(1, $result['new_items']);
        $this->assertEquals(0, $result['updated_items']);

        $this->em->flush();

        $items = $publication->getItems();
        $this->assertCount(1, $items);
        
        $item = $items->first();
        
        $this->assertEquals('new-item', $item->getGuid());
        $this->assertEquals('Test Item Title', $item->getTitle());
        $this->assertEquals('https://example.com/test-item', $item->getUrl());
        $this->assertEquals('Test summary', $item->getSummary());
        $this->assertEquals('<p>Test HTML content</p>', $item->getContentHtml());
        $this->assertEquals('Test text content', $item->getContentText());
        $this->assertEquals('https://example.com/image.jpg', $item->getImage());
        $this->assertEquals('en', $item->getLanguage());
        
        $itemPublishedAt = $item->getPublishedAt();
        $this->assertEquals($publishedAt->getTimestamp(), $itemPublishedAt->getTimestamp());
        
        $this->assertEquals(['Test Author'], $item->getAuthors());
        $this->assertEquals(['test-tag'], $item->getTags());
        
        $itemPublication = $item->getPublication();
        $this->assertEquals($publication->getId(), $itemPublication->getId());
    }

    public function test_processItems_updates_existing_items_with_correct_values(): void
    {
        $publication = PublicationFactory::createOne()->_real();
        $this->em->flush();

        $existingItem = ItemFactory::createOne([
            'publication' => $publication,
            'guid' => 'existing-item',
            'title' => 'Old Title',
            'url' => 'https://example.com/old-url',
            'summary' => 'Old summary',
            'content_html' => '<p>Old content</p>',
            'image' => 'https://example.com/old-image.jpg',
            'language' => 'en',
            'authors' => ['Old Author'],
            'tags' => ['old-tag'],
        ]);

        $this->em->flush();

        $newPublishedAt = new \DateTimeImmutable('-1 hour');
        
        $feed = $this->createTestFeed([
            $this->createTestParsedItem(
                id: 'existing-item',
                title: 'Updated Title',
                url: 'https://example.com/updated-url',
                summary: 'Updated summary',
                contentHtml: '<p>Updated content</p>',
                image: 'https://example.com/updated-image.jpg',
                language: 'fr',
                publishedAt: $newPublishedAt,
                authors: [new Author('Updated Author', null, null)],
                tags: [new Tag('updated-tag')]
            ),
        ]);

        $result = $this->fetchService->processItems($publication, $feed);

        $this->assertEquals(0, $result['new_items']);
        $this->assertEquals(1, $result['updated_items']);

        $this->em->flush();
        
        $freshItem = $this->em->getRepository(Item::class)->find($existingItem->getId());
        
        $this->assertEquals('Updated Title', $freshItem->getTitle());
        $this->assertEquals('https://example.com/updated-url', $freshItem->getUrl());
        $this->assertEquals('Updated summary', $freshItem->getSummary());
        $this->assertEquals('<p>Updated content</p>', $freshItem->getContentHtml());
        $this->assertEquals('https://example.com/updated-image.jpg', $freshItem->getImage());
        $this->assertEquals('fr', $freshItem->getLanguage());
        
        $freshPublishedAt = $freshItem->getPublishedAt();
        $this->assertEquals($newPublishedAt->getTimestamp(), $freshPublishedAt->getTimestamp());
        
        $this->assertEquals(['Updated Author'], $freshItem->getAuthors());
        $this->assertEquals(['updated-tag'], $freshItem->getTags());
        
        $itemPublication = $freshItem->getPublication();
        $this->assertEquals($publication->getId(), $itemPublication->getId());
    }

    private function createTestFeed(array $items): Feed
    {
        return new Feed(
            type: FeedType::RSS,
            version: '2.0',
            title: 'Test Feed',
            homepage_url: 'https://example.com',
            feed_url: 'https://example.com/feed',
            description: 'Test Description',
            items: $items
        );
    }

    private function createTestParsedItem(
        string $id,
        string $title,
        ?string $url = null,
        ?string $summary = null,
        ?string $contentHtml = null,
        ?string $contentText = null,
        ?string $image = null,
        ?string $language = null,
        ?\DateTimeImmutable $publishedAt = null,
        ?\DateTimeImmutable $updatedAt = null,
        array $authors = [],
        array $tags = []
    ): ParsedItem {
        return new ParsedItem(
            id: $id,
            url: $url ?? 'https://example.com/items/' . $id,
            title: $title,
            content_html: $contentHtml,
            content_text: $contentText,
            summary: $summary,
            image: $image,
            published_at: $publishedAt,
            updated_at: $updatedAt,
            authors: $authors,
            tags: $tags,
            language: $language
        );
    }
}
