<?php

namespace App\Enum;

enum FetchStatusEnum: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
} 