<?php

namespace Hyvor\FeedParser\Parser;


use App\Domain\FeedParser\Feed\Feed;

interface ParserInterface
{
    public function __construct(string $content);

    public function parse(): Feed;
}
