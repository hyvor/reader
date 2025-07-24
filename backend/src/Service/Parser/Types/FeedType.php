<?php

namespace App\Service\Parser\Types;

use Symfony\Component\HttpFoundation\Response;

enum FeedType: string
{
    case RSS = 'rss';
    case ATOM = 'atom';
    case JSONFEED = 'jsonfeed';

    public static function fromResponse(Response $response): ?self
    {
        $contentType = $response->headers->get('Content-Type');

        $type = null;
        if ($contentType !== null) {
            $type = FeedType::fromContentType($contentType);
        }
        
        if ($type === null) {
            $content = $response->getContent();
            if ($content !== false) {
                $type = FeedType::fromContent($content);
            }
        }

        return $type;
    }

    public static function fromContentType(string $contentType): ?self
    {
        if (str_contains($contentType, 'json')) {
            return self::JSONFEED;
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

        if ($xml !== false) {
            $root = $xml->getName();

            if ($root === 'rss') {
                return self::RSS;
            } elseif ($root === 'feed') {
                return self::ATOM;
            }
        } else {
            json_decode($content);
            if (json_last_error() === JSON_ERROR_NONE) {
                return self::JSONFEED;
            }
        }

        return null;
    }
}
