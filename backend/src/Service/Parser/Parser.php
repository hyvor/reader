<?php

namespace App\Service\Parser;

use App\Service\Parser\Types\Feed;

class Parser implements ParserInterface
{
    private string $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function parse(): Feed
    {
        $parser = $this->detectParser();
        return $parser->parse();
    }

    private function detectParser(): ParserInterface
    {
        json_decode($this->content);
        if (json_last_error() === JSON_ERROR_NONE) {
            return new JsonFeedParser($this->content);
        }

        $xml = @simplexml_load_string($this->content);
        if ($xml === false) {
            throw new ParserException('Content is neither JSON nor valid XML');
        }

        if ($xml->getName() === 'rss') {
            return new RSSParser($this->content);
        }

        if ($xml->getName() === 'feed') {
            return new AtomParser($this->content);
        }

        throw new ParserException('Unknown feed type. Supported types are RSS, Atom, and JSON Feed');
    }
} 