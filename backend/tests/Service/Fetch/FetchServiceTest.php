<?php

namespace App\Tests\Service\Fetch;

use App\Entity\Collection;
use App\Entity\Publication;
use App\Entity\Item;
use App\Service\Fetch\FetchService;
use App\Factory\PublicationFactory;
use App\Factory\CollectionFactory;
use App\Factory\ItemFactory;
use App\Tests\Case\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

class FetchServiceTest extends KernelTestCase
{
    use Factories;

    private Collection $collection;
    private FetchService $fetchService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fetchService = $this->container->get(FetchService::class);

        /** @var CollectionFactory $collectionFactory */
        $collectionFactory = $this->container->get(CollectionFactory::class);
        $this->collection = $collectionFactory->createOne();
    }

    public function test_calculate_average_publication_interval_with_insufficient_data(): void
    {
        $publication = PublicationFactory::createOne();

        ItemFactory::createOne([
            'publication' => $publication,
            'publishedAt' => new \DateTimeImmutable('-1 hour'),
        ]);

        $result = $this->fetchService->calculateAveragePublicationInterval($publication->_real());

        $this->assertNull($result);
    }

    public function test_calculate_average_publication_interval_with_no_items(): void
    {
        /** @var Publication $publication */
        $publication = PublicationFactory::createOne([
            'collection' => $this->collection,
        ]);

        $result = $this->fetchService->calculateAveragePublicationInterval($publication->_real());

        $this->assertNull($result);
    }

    public function test_calculate_average_publication_interval_with_regular_posting(): void
    {
        /** @var Publication $publication */
        $publication = PublicationFactory::createOne([
            'collection' => $this->collection,
        ]);

        $baseTime = new \DateTimeImmutable();
        for ($i = 0; $i < 5; $i++) {
            ItemFactory::createOne([
                'publication' => $publication,
                'publishedAt' => $baseTime->modify("-{$i} * 2 hours"),
            ]);
        }

        $result = $this->fetchService->calculateAveragePublicationInterval($publication->_real());

        $this->assertEqualsWithDelta(120, $result, 5);
    }

    public function test_calculate_average_publication_interval_ignores_items_without_published_at(): void
    {
        /** @var Publication $publication */
        $publication = PublicationFactory::createOne([
            'collection' => $this->collection,
        ]);

        for ($i = 0; $i < 3; $i++) {
            ItemFactory::createOne([
                'publication' => $publication,
                'publishedAt' => null,
            ]);
        }

        $baseTime = new \DateTimeImmutable();
        for ($i = 0; $i < 4; $i++) {
            ItemFactory::createOne([
                'publication' => $publication,
                'publishedAt' => $baseTime->modify("-{$i} * 3 hours"),
            ]);
        }

        $result = $this->fetchService->calculateAveragePublicationInterval($publication->_real());

        $this->assertEqualsWithDelta(180, $result, 5);
    }

    public function test_update_adaptive_interval_with_bounds_minimum(): void
    {
        /** @var Publication $publication */
        $publication = PublicationFactory::createOne([
            'collection' => $this->collection,
        ]);

        $baseTime = new \DateTimeImmutable();
        for ($i = 0; $i < 10; $i++) {
            ItemFactory::createOne([
                'publication' => $publication,
                'publishedAt' => $baseTime->modify("-{$i} * 5 minutes"),
            ]);
        }

        $this->fetchService->updateAdaptiveInterval($publication->_real());

        $this->assertEquals(15, $publication->getInterval());
    }

    public function test_update_adaptive_interval_with_bounds_maximum(): void
    {
        /** @var Publication $publication */
        $publication = PublicationFactory::createOne([
            'collection' => $this->collection,
        ]);

        $baseTime = new \DateTimeImmutable();
        for ($i = 0; $i < 5; $i++) {
            ItemFactory::createOne([
                'publication' => $publication,
                'publishedAt' => $baseTime->modify("-{$i} * 48 hours"),
            ]);
        }

        $this->fetchService->updateAdaptiveInterval($publication->_real());

        $this->assertEquals(1440, $publication->getInterval());
    }

    public function test_update_next_fetch_time(): void
    {
        /** @var Publication $publication */
        $publication = PublicationFactory::createOne([
            'collection' => $this->collection,
            'interval' => 120, 
        ]);

        $beforeUpdate = new \DateTimeImmutable();
        
        $this->fetchService->updateNextFetchTime($publication->_real());

        $afterUpdate = new \DateTimeImmutable();
        
        $expectedNextFetch = $beforeUpdate->modify('+120 minutes');
        $actualNextFetch = $publication->getNextFetchAt();

        $this->assertGreaterThan($beforeUpdate, $actualNextFetch);
        $this->assertLessThan($afterUpdate->modify('+125 minutes'), $actualNextFetch);
        $this->assertEqualsWithDelta(
            $expectedNextFetch->getTimestamp(),
            $actualNextFetch->getTimestamp(),
            60
        );
    }

    public function test_find_due_for_fetching(): void
    {
        $now = new \DateTimeImmutable();

        $duePub1 = PublicationFactory::createOne([
            'collection' => $this->collection,
            'nextFetchAt' => $now->modify('-30 minutes'),
        ]);

        $duePub2 = PublicationFactory::createOne([
            'collection' => $this->collection,
            'nextFetchAt' => $now->modify('-1 minute'),
        ]);

        $notDuePub = PublicationFactory::createOne([
            'collection' => $this->collection,
            'nextFetchAt' => $now->modify('+30 minutes'),
        ]);

        $duePublications = $this->fetchService->findDueForFetching($now);

        $this->assertCount(2, $duePublications);
        
        $dueIds = array_map(fn($pub) => $pub->getId(), $duePublications);
        $this->assertContains($duePub1->getId(), $dueIds);
        $this->assertContains($duePub2->getId(), $dueIds);
        $this->assertNotContains($notDuePub->getId(), $dueIds);
    }
} 