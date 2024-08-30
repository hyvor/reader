<?php

use App\Domain\Feed\Exception\FeedFetchException;
use App\Domain\Feed\FeedService;
use app\Domain\FeedFetch\Fetch;
use App\Domain\FeedParser\Parser\ParserException;
use App\Domain\FeedSubscription\FeedSubscriptionService;
use App\Domain\User\UserService;
use Hyvor\FeedParser\Parser;
use Hyvor\Internal\Http\Exceptions\HttpException;
use Hyvor\Internal\Http\Middleware\AccessAuthUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Objects\FeedObject;

class FeedController
{

    public function addFeed(Request $request, AccessAuthUser $user): JsonResponse
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $url = (string)$request->string('url');

        $user = UserService::byHyvorUserId($user->id);

        if ($user === null) {
            $user = UserService::createUser($user->id);
        }

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
