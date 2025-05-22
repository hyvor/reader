<?php

namespace App\Entity;

use App\Repository\ItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
class Item
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private string $url;

    #[ORM\Column]
    private string $title;

    #[ORM\Column(nullable: true)]
    private ?string $content_html = null;

    #[ORM\Column(nullable: true)]
    private ?string $content_text = null;

    #[ORM\Column(nullable: true)]
    private ?string $summary = null;

    #[ORM\Column(nullable: true)]
    private ?string $image = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $published_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $authors = [];

    #[ORM\Column(type: Types::ARRAY)]
    private array $tags = [];

    #[ORM\Column(nullable: true)]
    private ?string $language = null;
}
