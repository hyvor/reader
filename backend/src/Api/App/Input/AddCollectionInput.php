<?php

namespace App\Api\App\Input;

use Symfony\Component\Validator\Constraints as Assert;

class AddCollectionInput
{
    #[Assert\NotBlank]
    public string $name = '';

    #[Assert\Type('bool')]
    public bool $is_public = false;
}