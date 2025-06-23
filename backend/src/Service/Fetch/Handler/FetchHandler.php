<?php

namespace App\Service\Fetch\Handler;

use App\Service\Fetch\Message\FetchMessage;
use App\Service\Fetch\Message\ProcessFeedMessage;
use App\Service\Fetch\FetchService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class FetchHandler
{
    public function __construct(
        private FetchService $fetchService,
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(FetchMessage $message): void
    {
        $duePublications = $this->fetchService->findDueForFetching(new \DateTimeImmutable());
        
        if (count($duePublications) === 0) {
            return;
        }

        foreach ($duePublications as $publication) {
            $this->messageBus->dispatch(new ProcessFeedMessage($publication->getId()));
            $this->updateNextFetchTime($publication);
        }

        $this->entityManager->flush();
    }

    private function updateNextFetchTime($publication): void
    {
        $nextFetchAt = (new \DateTimeImmutable())->modify("+{$publication->getInterval()} minutes");
        $publication->setNextFetchAt($nextFetchAt);
        $publication->setUpdatedAt(new \DateTimeImmutable());
    }
} 