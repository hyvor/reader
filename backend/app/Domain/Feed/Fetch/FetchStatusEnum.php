<?php

namespace app\Domain\Feed\Fetch;

enum FetchStatusEnum: string
{

    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

}
