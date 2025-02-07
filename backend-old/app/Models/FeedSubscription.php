<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedSubscription extends Model
{
    use HasFactory;

    public function feed()
    {
        return $this->belongsTo(Feed::class);
    }
}
