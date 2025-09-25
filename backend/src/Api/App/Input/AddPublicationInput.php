<?php

namespace App\Api\App\Input;

use Symfony\Component\Validator\Constraints as Assert;

final class AddPublicationInput
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly string $collection_slug = '',

        #[Assert\NotBlank]
        #[Assert\Url]
        public readonly string $url = '',
    ) {
    }
}


