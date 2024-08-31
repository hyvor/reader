<?php

use App\Http\AppApi\Controllers\AppController;
use App\Http\AppApi\Middleware\EnsureUser;
use Hyvor\Internal\Http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/app')
    ->middleware(AuthMiddleware::class)
    ->group(function () {
        Route::middleware(EnsureUser::class)->group(function () {
            Route::get('/init', [AppController::class, 'init']);
            Route::post('/feed', [FeedController::class, 'addFeed']);
        });
    });
