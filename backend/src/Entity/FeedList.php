<?php

namespace App\Entity;

use App\Repository\FeedListRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FeedListRepository::class)]
class FeedList
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id = null;

    #[ORM\Column]
    private string $name = null;
}
