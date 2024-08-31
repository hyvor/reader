<?php

namespace App\Domain\User;

use App\Models\User;

class UserService
{

    public static function byId(int $hyvorUserId): ?User
    {
        return User::find($hyvorUserId);
    }

    public static function createUser(int $hyvorUserId): User
    {
        return User::create([
            'id' => $hyvorUserId,
        ]);
    }

    public static function getOrCreateUser(int $hyvorUserId): User
    {
        $user = self::byId($hyvorUserId);
        if ($user) {
            return $user;
        }
        return self::createUser($hyvorUserId);
    }

}
