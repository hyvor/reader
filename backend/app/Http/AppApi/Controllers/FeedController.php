<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedController
{

    public function addFeed(Request $request): JsonResponse
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $url = (string)$request->string('url');
        //
    }

}
