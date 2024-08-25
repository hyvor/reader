<?php

namespace App\Domain\FeedParser\Parser;

use App\Domain\FeedParser\Feed\Feed;

class RssParser implements ParserInterface
{

    public function __construct(string $content)
    {
    }

    public function parse(): Feed
    {
        // TODO: Implement parse() method.
    }
}
