<?php


namespace App\Domain\FeedParser\Parser;

use App\Domain\FeedParser\Feed\Feed;
use SimpleXMLElement;

class AtomParser implements ParserInterface
{

    private SimpleXMLElement $xml;

    public function __construct(string $content)
    {
        $this->xml = new SimpleXMLElement($content);
    }

    public function parse(): Feed
    {
        dd($this->xml->title);
    }

}
