<?php

namespace App\Domain\Feed\Parser;


use App\Domain\Feed\Feed\Feed;

interface ParserInterface
{
    public function __construct(string $content);

    public function parse(): Feed;
}
