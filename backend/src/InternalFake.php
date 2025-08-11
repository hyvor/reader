<?php

namespace App;

use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\InternalFake as BaseInternalFake;

/**
 * @phpstan-import-type AuthUserArrayPartial from AuthUser
 */
class InternalFake extends BaseInternalFake
{
    public function user(): AuthUser
    {
        return AuthUser::fromArray([
            'id' => 5,
            'username' => 'sakithb',
            'name' => 'Sakith B.',
            'email' => 'sakith@hyvor.com',
            'picture_url' => 'https://hyvor.com/avatar.jpg',
        ]);
    }

    /**
     * @return array<int, AuthUser|AuthUserArrayPartial>|null
     */
    public function usersDatabase(): ?array
    {
        return [
            [
                'id' => 1,
                'username' => 'supun',
                'name' => 'Supun Wimalasena',
            ],
            [
                'id' => 2,
                'username' => 'ishini',
                'name' => 'Ishini Senanayake',
            ],
            [
                'id' => 3,
                'username' => 'nadil',
                'name' => 'Nadil Karunaratne',
            ],
            [
                'id' => 4,
                'username' => 'thibault',
                'name' => 'Thibault Boutet',
            ],
            [
                'id' => 5,
                'username' => 'sakithb',
                'name' => 'Sakith B.',
                'email' => 'sakith@hyvor.com',
                'picture_url' => 'https://hyvor.com/avatar.jpg',
            ]
        ];
    }
} 
