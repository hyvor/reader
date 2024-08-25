<?php

use App\Domain\FeedParser\Parser\JsonParser;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

include 'api-app.php';

Route::get('/api/init', function () {
    return response()->json([
        'message' => 'Backend Healthy'
    ]);
});

Route::get('/api/test', function () {
    $url = 'https://www.jsonfeed.org/feed.json';
    $url = 'https://micro.blog/feeds/manton.json';
    $file = file_get_contents($url);
    $parser = new JsonParser($file);
    $feed = $parser->parse();

    return response()->json($feed);
});
