<?php

namespace App\Api\App\Authorization;

use Hyvor\Internal\Auth\Auth;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Auth\AuthUser;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::CONTROLLER, priority: 200)]
class AuthorizationListener
{
    public const string RESOLVED_USER_ATTRIBUTE_KEY = 'app_api_resolved_user';

    public function __construct(
        private readonly AuthInterface $auth,
    ) {
    }

    public function __invoke(ControllerEvent $event): void
    {
        if ($event->isMainRequest() === false) {
            return;
        }

        $request = $event->getRequest();

        $path = $request->getPathInfo();
        if (!str_starts_with($path, '/api/app')) {
            return;
        }

        if ($request->getMethod() === 'OPTIONS') {
            return;
        }

        $cookie = (string)$request->cookies->get(Auth::HYVOR_SESSION_COOKIE_NAME, '');
        $user = $this->auth->check($cookie);

        if ($user === false) {
            throw new HttpException(401, 'Unauthorized');
        }

        $request->attributes->set(self::RESOLVED_USER_ATTRIBUTE_KEY, $user);
    }

    public static function hasUser(Request $request): bool
    {
        return $request->attributes->has(self::RESOLVED_USER_ATTRIBUTE_KEY);
    }

    public static function getUser(Request $request): AuthUser
    {
        $user = $request->attributes->get(self::RESOLVED_USER_ATTRIBUTE_KEY);
        assert($user instanceof AuthUser, 'User must be an instance of AuthUser');
        return $user;
    }
}


