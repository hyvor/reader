<?php

use App\Http\AppApi\Controllers\AppController;
use App\Http\AppApi\Controllers\FeedItemsController;
use App\Http\AppApi\Middleware\EnsureUser;
use Hyvor\Internal\Http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/app')
    ->middleware(AuthMiddleware::class)
    ->group(function () {
        // TODO: Do not create user here
        // When logging in for the first time, show a modal
        Route::middleware(EnsureUser::class)->group(function () {
            Route::get('/init', [AppController::class, 'init']);
            Route::post('/feed', [FeedController::class, 'addFeed']);

            Route::get('/items', [FeedItemsController::class, 'getItems']);
        });
    });
