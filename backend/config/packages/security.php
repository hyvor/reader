<?php

use Hyvor\Internal\Bundle\Security\HyvorAuthenticator;
use Hyvor\Internal\Bundle\Security\UserRole;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Config\SecurityConfig;

return static function (ContainerBuilder $container, SecurityConfig $security): void {
    // Password hashers
    $security
        ->passwordHasher('Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface')
        ->algorithm('auto');

    // Providers
    $security
        ->provider('app_user_provider')
        ->id('App\Security\UserProvider');

    // Firewalls
    $security
        ->firewall('dev')
        ->pattern('^/(_(profiler|wdt)|css|images|js)/')
        ->security(false);

    $security
        ->firewall('hyvor_auth')
        ->stateless(true)
        ->lazy(true)
        ->customAuthenticators([HyvorAuthenticator::class]);

    $security
        ->firewall('main')
        ->lazy(true)
        ->provider('app_user_provider');

    // Access control
    $security
        ->accessControl()
        ->path('^/api')
        ->roles(UserRole::HYVOR_USER);
}; 