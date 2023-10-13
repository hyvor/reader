<?php

namespace Hyvor\FeedParser;

use DateTimeInterface;
use Hyvor\FeedParser\Atom\Author;
use Hyvor\FeedParser\Atom\Category;
use Hyvor\FeedParser\Atom\Entry;
use Hyvor\FeedParser\Atom\Link;
use Hyvor\FeedParser\Atom\Person;

class Feed
{

    /**
     * FeedTypeEnum::RSS
     * FeedTypeEnum::ATOM
     * FeedTypeEnum::JSON_FEED
     */
    public FeedTypeEnum $type;

    /**
     * Version of the feed under the type
     *
     * Atom: "1.0"
     * RSS: Taken from <rss version="2.0">
     * JSON Feed: "1.1"
     */
    public string $version;

    /**
     * Atom: <feed><title>
     * RSS: <channel><title>
     * JSON Feed: title
     */
    public string $title;

    /**
     * Atom: <feed><link> without rel or with rel="alternate"
     * RSS: <channel><link>
     * JSON Feed: home_page_url
     */
    public string $homepageUrl;

    /**
     * Atom: <feed><link rel="self">
     * RSS: null
     * JSON Feed: feed_url
     */
    public string $feedUrl;

    /**
     * Atom: <feed><subtitle>
     * RSS: <channel><description>
     * JSON Feed: description
     */
    public string $description;

    /**
     * Atom: <feed><generator>
     * RSS: <channel><generator>
     * JSON Feed: null
     */
    public string $generator;

    /**
     * Language
     * Atom: null
     * RSS: <channel><language>
     * JSON Feed: language
     */
    public string $language;

    /**
     * Atom: <feed><updated>
     * RSS: <channel><lastBuildDate>
     * JSON Feed: null
     */
    public DateTimeInterface $updated;

    /**
     * icon
     * Atom: <feed><icon>
     * RSS: <channel><image><url>
     * JSON Feed: icon or favicon
     */
    public string $icon;

    /**
     * @var Author[]
     */
    public array $authors = [];

    /**
     * @var Item[]
     */
    public array $items = [];

}