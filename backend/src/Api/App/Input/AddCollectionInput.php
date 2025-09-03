<?php

namespace App\Api\App\Input;

use Symfony\Component\Validator\Constraints as Assert;

final class AddCollectionInput
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly string $name = '',

        #[Assert\Type('bool')]
        public readonly bool $is_public = false,
    ) {
    }
}


