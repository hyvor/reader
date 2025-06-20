<?php

namespace App\Service\Fetch;

enum FetchStatusEnum: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
} 