<?php

namespace App\Domain\Feed\Parser;

use App\Domain\Feed\Feed\Feed;

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
