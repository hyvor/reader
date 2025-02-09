<?php

namespace App\Entity;

use App\Repository\FeedRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FeedRepository::class)]
class Feed
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTime $createdAt;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTime $updatedAt;

    #[ORM\Column(type: 'string', unique: true)]
    private string $url;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'integer', options: ['default' => 60])]
    private int $interval = 60;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $lastFetchedAt = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $nextFetchAt;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $subscribers = 0;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $conditionalGetLastModified = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $conditionalGetEtag = null;

}
