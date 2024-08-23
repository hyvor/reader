<?php

namespace Hyvor\FeedParser\Parser;

use Hyvor\FeedParser\Feed;

interface ParserInterface
{
    public function __construct(string $content);
    public function parse() : Feed;
}