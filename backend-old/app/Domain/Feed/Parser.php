<?php

namespace App\Domain\Feed;

use App\Domain\Feed\Feed\Feed;
use App\Domain\Feed\Parser\AtomParser;
use App\Domain\Feed\Parser\JsonParser;
use App\Domain\Feed\Exception\ParserException;
use App\Domain\Feed\Parser\RssParser;
use Illuminate\Http\Client\Response;

class Parser
{

    public function __construct(private string $content, private FeedType $type)
    {
    }

    public static function feed(string $content, FeedType $type): Feed
    {
        return (new self($content, $type))->parse();
    }

    /**
     * @throws ParserException
     */
    public function parse(): Feed
    {
        $parser = match ($this->type) {
            FeedType::RSS => new RssParser($this->content),
            FeedType::ATOM => new AtomParser($this->content),
            FeedType::JSON => new JsonParser($this->content),
        };

        return $parser->parse();
    }

    public static function from(string $content, FeedType $type): Feed
    {
        $obj = new self($content, $type);
        return $obj->parse();
    }

    /**
     * @throw ParserException
     */
    public static function fromResponse(Response $response): Feed
    {
        $type = FeedType::fromResponse($response);

        if ($type === null) {
            throw new ParserException('Unable to determine feed type');
        }

        return self::feed($response->body(), $type);
    }


}
