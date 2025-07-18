<?php

namespace App;

use App\Service\Fetch\Message\FetchMessage;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule as SymfonySchedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule('default')]
class Schedule implements ScheduleProviderInterface
{
    public function __construct(
        private LockFactory $lockFactory,
    ) {
    }

    public function getSchedule(): SymfonySchedule
    {
        return (new SymfonySchedule())
            ->with(
                RecurringMessage::every('1 minute', new FetchMessage())
            )
            ->lock($this->lockFactory->createLock('fetch-scheduler'));
    }
}
