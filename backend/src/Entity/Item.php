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
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 10000, nullable: true)]
    private ?string $content_html = null;

    #[ORM\Column(length: 10000, nullable: true)]
    private ?string $content_text = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $summary = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $published_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $authors = [];

    #[ORM\Column(type: Types::ARRAY)]
    private array $tags = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $language = null;
}
