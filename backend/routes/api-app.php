<?php

use Hyvor\Internal\Http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/app')
    ->middleware(AuthMiddleware::class)
    ->group(function () {
        Route::post('/feed', [FeedController::class, 'addFeed']);
    });
