<?php

namespace App\Service\Parser;

use App\Service\Parser\Types\Feed;

interface ParserInterface
{
    public function __construct(string $content);

    public function parse(): Feed;
}
