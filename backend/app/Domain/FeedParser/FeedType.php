<?php

namespace App\Domain\FeedParser;

enum FeedType: string
{

    case RSS = 'rss';
    case ATOM = 'atom';
    case JSON = 'json';

}
