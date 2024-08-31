<?php

namespace App\Http\AppApi\Middleware;

use App\Domain\User\UserService;
use App\Models\User;
use Hyvor\Internal\Http\Middleware\AccessAuthUser;
use Illuminate\Http\Request;

class EnsureUser
{

    private const USER_CONTAINER_KEY = 'ensure-user-model';

    /**
     * Makes sure that the user is added to the database
     * and sets the User model in the container
     */
    public function handle(Request $request, \Closure $next): mixed
    {
        $hyvorUser = app(AccessAuthUser::class);
        $user = UserService::getOrCreateUser($hyvorUser->id);
        app()->instance(self::USER_CONTAINER_KEY, $user);

        return $next($request);
    }

    public static function user(): User
    {
        return app(self::USER_CONTAINER_KEY);
    }

}
