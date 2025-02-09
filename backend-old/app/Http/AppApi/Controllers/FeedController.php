<?php

namespace App\Http\AppApi\Controllers;

use App\Domain\Feed\Exception\FeedFetchException;
use App\Domain\Feed\Exception\ParserException;
use App\Domain\Feed\FeedService;
use App\Domain\Feed\Parser;
use App\Domain\FeedFetch\Fetch;
use App\Domain\FeedSubscription\FeedSubscriptionService;
use App\Http\AppApi\Middleware\EnsureUser;
use App\Http\AppApi\Objects\FeedObject;
use Hyvor\Internal\Http\Exceptions\HttpException;
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

        $user = EnsureUser::user();

        $feed = FeedService::byUrl($url);

        if ($feed === null) {
            try {
                $response = Fetch::fetch($url);
                $parsedFeed = Parser::fromResponse($response);
            } catch (FeedFetchException|ParserException $e) {
                throw new HttpException($e->getMessage());
            }

            $feed = FeedService::createFeed($url, $parsedFeed);
        }

        FeedSubscriptionService::createSubscription($user, $feed);

        return response()->json(new FeedObject($feed));
    }

}
