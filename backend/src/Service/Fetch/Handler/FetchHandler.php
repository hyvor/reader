<?php

namespace App\Service\Fetch\Handler;

use App\Service\Fetch\Message\FetchMessage;
use App\Service\Fetch\Message\ProcessFeedMessage;
use App\Service\Fetch\FetchService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Doctrine\ORM\EntityManagerInterface;

#[AsMessageHandler]
class FetchHandler
{
    public function __construct(
        private FetchService $fetchService,
        private MessageBusInterface $messageBus,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(FetchMessage $message): void
    {
        $duePublications = $this->fetchService->findDueForFetching(new \DateTimeImmutable());

        foreach ($duePublications as $publication) {
            $publication->setIsFetching(true);
            $this->messageBus->dispatch(new ProcessFeedMessage($publication->getId()));
        }

        $this->entityManager->flush();
    }


} 