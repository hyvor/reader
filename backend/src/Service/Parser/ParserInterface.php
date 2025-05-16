<?php

namespace App\Service\Parser;

use App\Entity\Feed;

interface ParserInterface
{
    public function __construct(string $content);

    public function parse(): Feed;
}
