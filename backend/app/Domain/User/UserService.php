<?php

namespace App\Domain\User;

use App\Models\User;

class UserService
{

    public static function byHyvorUserId(int $hyvorUserId): ?User
    {
        return User::where('hyvor_user_id', $hyvorUserId)->first();
    }

    public static function createUser(int $hyvorUserId): User
    {
        return User::create([
            'hyvor_user_id' => $hyvorUserId,
        ]);
    }

}
