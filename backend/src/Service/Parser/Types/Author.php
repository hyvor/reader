<?php

namespace App\Service\Parser\Types;

class Author
{


    public function __construct(

        /**
         * Name of the author
         *
         * Atom: <author><n>
         * RSS: <item><author>
         * JSON Feed: author.name
         */
        public string $name,

        /**
         * URL of the author
         *
         * Atom: <author><uri>
         * RSS: null
         * JSON Feed: author.url
         */
        public ?string $url,

        /**
         * Avatar of the author
         *
         * Atom: null
         * RSS: null
         * JSON Feed: author.avatar
         */
        public ?string $avatar,

    ) {
    }


}
