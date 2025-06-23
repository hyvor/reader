<?php

namespace App\Tests\Service\Fetch\Handler;

use App\Service\Fetch\Message\FetchMessage;
use App\Tests\Case\KernelTestCase;

class FetchHandlerTest extends KernelTestCase
{

    public function test_dispatches_process_feed_message_for_due_publications(): void
    {

        // seed publications

        $transport = $this->transport('scheduler_default');
        $transport->send(new FetchMessage());
        $transport->process();

        // assert that the messages were queued with correct publication IDs
    }

}