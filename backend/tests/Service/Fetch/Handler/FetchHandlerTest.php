<?php

namespace App\Tests\Service\Fetch\Handler;

use App\Entity\Collection;
use App\Service\Fetch\Message\FetchMessage;
use App\Factory\PublicationFactory;
use App\Factory\CollectionFactory;
use App\Service\Fetch\Message\ProcessFeedMessage;
use App\Tests\Case\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Messenger\Test\Transport\TestTransport;

class FetchHandlerTest extends KernelTestCase
{
    use Factories;

    private Collection $collection;
    private TestTransport $schedulerTransport;
    private TestTransport $asyncTransport;
    private PublicationFactory $publicationFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->schedulerTransport = $this->transport('scheduler_default');
        $this->asyncTransport = $this->transport('async');

        $this->em->createQuery('DELETE FROM App\Entity\PublicationFetch')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\Item')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\Publication')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\Collection')->execute();

        /** @var CollectionFactory $collectionFactory */
        $collectionFactory = $this->container->get(CollectionFactory::class);
        $this->collection = $collectionFactory->createOne();

        /** @var PublicationFactory $publicationFactory */
        $this->publicationFactory = $this->container->get(PublicationFactory::class);
    }

    public function test_not_dispatches_process_feed_message_for_not_due_publications(): void
    {
        $publication = $this->publicationFactory->createOne([
            'collection' => $this->collection,
            'nextFetchAt' => new \DateTimeImmutable('+30 minutes'),
            'interval' => 60,
        ]);

        $this->schedulerTransport->send(new FetchMessage());
        $this->schedulerTransport->process();
        $this->asyncTransport->queue()->assertEmpty();
    }

    public function test_dispatches_process_feed_message_for_due_publications(): void
    {
        /** @var PublicationFactory $publicationFactory */
        $publicationFactory = $this->container->get(PublicationFactory::class);
        $publication = $this->publicationFactory->createOne([
            'collection' => $this->collection,
            'nextFetchAt' => new \DateTimeImmutable('-30 minutes'),
            'interval' => 60,
        ]);

        $this->schedulerTransport->send(new FetchMessage());
        $this->schedulerTransport->process();
        $this->asyncTransport->queue()->assertCount(1);

        /** @var ProcessFeedMessage $message */
        $message = $this->asyncTransport->queue()->first()->getMessage();
        $this->assertInstanceOf(ProcessFeedMessage::class, $message);
        $this->assertEquals($publication->getId(), $message->publicationId);
    }
}
