<?php

namespace App\Service\Fetch\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(transport: 'async')]
class ProcessFeedMessage
{
    public function __construct(
        public readonly int $publicationId
    ) {
    }
} 