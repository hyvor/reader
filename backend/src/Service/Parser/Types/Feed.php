<?php

namespace App\Service\Parser\Types;

use App\Service\Parser\Types\FeedType;
use DateTimeInterface;

class Feed
{

    public function __construct(

        /**
         * Type of the feed
         */
        public FeedType $type,

        /**
         * Version of the feed under the type
         *
         * Atom: "1.0"
         * RSS: Taken from <rss version="2.0">
         * JSON Feed: Taken from { "version": "https://jsonfeed.org/version/1.1" } (default: 1.1)
         */
        public string $version,

        /**
         * Atom: <feed><title>
         * RSS: <channel><title>
         * JSON Feed: title
         */
        public string $title,

        /**
         * Atom: <feed><link> without rel or with rel="alternate"
         * RSS: <channel><link>
         * JSON Feed: home_page_url
         */
        public string $homepage_url,

        /**
         * Atom: <feed><link rel="self">
         * RSS: null
         * JSON Feed: feed_url
         */
        public ?string $feed_url,

        /**
         * Atom: <feed><subtitle>
         * RSS: <channel><description>
         * JSON Feed: description
         */
        public ?string $description,

        /**
         * icon
         * Atom: <feed><icon>
         * RSS: <channel><image><url>
         * JSON Feed: favicon if set, otherwise icon
         */
        public ?string $icon = null,

        /**
         * Language
         * Atom: null
         * RSS: <channel><language>
         * JSON Feed: language
         */
        public ?string $language = null,

        /**
         * Atom: <feed><updated>
         * RSS: <channel><lastBuildDate>
         * JSON Feed: null
         */
        public ?DateTimeInterface $updated_at = null,

        /**
         * Atom: <feed><generator>
         * RSS: <channel><generator>
         * JSON Feed: null
         */
        public ?string $generator = null,

        /**
         * @var Item[]
         */
        public array $items = [],

    ) {
    }


}
