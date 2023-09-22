<?php

namespace Hyvor\FeedParser;

use DateTimeInterface;
use Hyvor\FeedParser\Atom\Category;
use Hyvor\FeedParser\Atom\Entry;
use Hyvor\FeedParser\Atom\Link;
use Hyvor\FeedParser\Atom\Person;

class Feed
{

    public string $id;

    public string $title;

    /**
     * <link rel="self"> in Atom
     */
    public string $link;

    public DateTimeInterface $updated;
    public ?string $subtitle = null;
    public ?string $icon = null;
    public ?string $logo = null;
    public ?string $generator = null;
    public ?string $rights = null;

    /** @var Category[] */
    public array $categories = [];

    /** @var Link[] */
    public array $links = [];

    /** @var Person[] */
    public array $authors = [];

    /** @var Person[] */
    public array $contributors = [];

    /** @var Entry[] */
    public array $entries = [];

}