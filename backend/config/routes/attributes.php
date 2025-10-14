<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {

    // app API
    $routes->import('../../src/Api/App/Controller', 'attribute')
        ->prefix('/api/app')
        ->namePrefix('api_app_');

    // OIDC routes
    $routes->import('@InternalBundle/src/Controller/OidcController.php', 'attribute')
        ->prefix('/api/oidc')
        ->namePrefix('api_oidc_');

};