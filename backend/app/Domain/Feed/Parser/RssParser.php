<?php

namespace App\Domain\Feed\Parser;

use App\Domain\Feed\Feed\Feed;

class RssParser implements ParserInterface
{

    public function __construct(string $content)
    {
        $xml = simplexml_load_string($content, \SimpleXMLElement::class, LIBXML_NOCDATA);
        $json = json_encode($xml);
        $array = json_decode($json, true);
        dd($array);
    }

    public function parse(): Feed
    {
        // TODO: Implement parse() method.
    }
}
