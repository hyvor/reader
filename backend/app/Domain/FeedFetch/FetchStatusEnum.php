<?php

namespace app\Domain\FeedFetch;

enum FetchStatusEnum: string
{

    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

}
