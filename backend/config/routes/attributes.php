<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {

    // app API
    $routes->import('../../src/Api/App/Controller', 'attribute')
        ->prefix('/api/app')
        ->namePrefix('api_app_');

    //

};