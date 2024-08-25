<?php

namespace App\Domain\FeedParser;

use Illuminate\Http\Client\Response;

enum FeedType: string
{

    case RSS = 'rss';
    case ATOM = 'atom';
    case JSON = 'json';

    public static function fromResponse(Response $response): ?self
    {
        $contentType = $response->header('Content-Type');

        $type = FeedType::fromContentType($contentType);
        if ($type === null) {
            $type = FeedType::fromContent($response->body());
        }

        return $type;
    }

    public static function fromContentType(string $contentType): ?self
    {
        if (str_contains('json', $contentType)) {
            return self::JSON;
        } elseif (str_contains('application/atom+xml', $contentType)) {
            return self::ATOM;
        } elseif (str_contains('application/rss+xml', $contentType)) {
            return self::RSS;
        }

        return null;
    }

    public static function fromContent(string $content): ?self
    {
        libxml_use_internal_errors(true);

        $xml = simplexml_load_string($content);

        if ($xml === false) {
            return null;
        }

        $root = $xml->getName();

        if ($root === 'rss') {
            return self::RSS;
        } elseif ($root === 'feed') {
            return self::ATOM;
        }

        return null;
    }

}
