<?php

namespace App\Api\App\Input;

use Symfony\Component\Validator\Constraints as Assert;

class AddPublicationInput
{
    #[Assert\NotBlank]
    public string $collection_slug = '';

    #[Assert\NotBlank]
    #[Assert\Url]
    public string $url = '';
}