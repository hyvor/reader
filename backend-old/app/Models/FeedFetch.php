<?php

namespace App\Models;

use App\Domain\FeedFetch\FetchStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedFetch extends Model
{
    use HasFactory;

    protected $casts = [
        'status' => FetchStatusEnum::class,
    ];
}
