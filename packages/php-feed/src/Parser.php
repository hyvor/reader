<?php

namespace Hyvor\FeedParser;

class Parser
{

    public function __construct(private string $xml)
    {}

    public static function feed(string $xml): Feed
    {
        return (new self($xml))->parse();
    }

    public function parse() : Feed
    {

    }


}