<?php

namespace App\Domain\FeedFetch;

enum FetchStatusEnum: string
{

    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

}
