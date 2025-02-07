<?php

namespace App\Domain\Feed\Feed;

use DateTimeInterface;

class Item
{
    public function __construct(

        /**
         * This is the ID of the item.
         * It is unique for each item.
         * It can be used to track updates to an item.
         *
         * Atom: <entry><id>
         * RSS: <item><guid>
         * JSON Feed: item.id
         */
        public string $id,

        /**
         * Atom: <entry><link>
         * RSS: <item><link>
         * JSON Feed: item.url
         */
        public string $url,

        /**
         * Atom: <entry><title> (converted to plain text)
         * RSS: <item><title>
         * JSON Feed: item.title
         */
        public string $title,

        /**
         * Content of the item in HTML
         *
         * Atom: <entry><content type="html|xhtml">
         * RSS: null
         * JSON Feed: item.content_html
         */
        public ?string $content_html,


        /**
         * Content of the item in plain text
         *
         * Atom: <entry><content type="text"> (if not provided, it is converted from content_html)
         * RSS: null
         * JSON Feed: item.content_text
         */
        public ?string $content_text,

        /**
         * Atom: <entry><summary> (converted to plain text)
         * RSS: <item><description>
         * JSON Feed: item.summary
         */
        public ?string $summary,

        /**
         * Atom:
         *  The first <media:content> with type="image/*" or
         *  The first <img> in content_html
         *
         * RSS:
         *  The first <enclosure> with type="image/*" or
         *  The first <img> in content_html
         *
         * JSON Feed: item.image or item.banner_image
         */
        public ?string $image,

        /**
         * Atom: <entry><published>
         * RSS: <item><pubDate>
         * JSON Feed: item.date_published
         */
        public ?DateTimeInterface $published_at,

        /**
         * Atom: <entry><updated>
         * RSS: null
         * JSON Feed: item.date_modified
         */
        public ?DateTimeInterface $updated_at,

        /**
         * @var Author[]
         */
        public array $authors,

        /**
         * @var Tag[]
         */
        public array $tags,

        /**
         * Atom: null
         * RSS: null
         * JSON Feed: item.language
         */
        public ?string $language,
    ) {
    }


}
