<?php

use App\Domain\Feed\Exception\FeedFetchException;
use App\Domain\Feed\FeedService;
use App\Domain\Feed\Fetch;
use App\Domain\FeedParser\Parser\ParserException;
use Hyvor\FeedParser\Parser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Hyvor\Internal\Http\Exceptions\HttpException;
use Objects\FeedObject;

class FeedController
{

    public function addFeed(Request $request): JsonResponse
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $url = (string)$request->string('url');

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

        return response()->json(new FeedObject($feed));
    }

}
