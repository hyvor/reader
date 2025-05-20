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

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getInterval(): int
    {
        return $this->interval;
    }

    public function setInterval(int $interval): void
    {
        $this->interval = $interval;
    }

    public function getLastFetchedAt(): ?\DateTime
    {
        return $this->lastFetchedAt;
    }

    public function setLastFetchedAt(?\DateTime $lastFetchedAt): void
    {
        $this->lastFetchedAt = $lastFetchedAt;
    }

    public function getNextFetchAt(): \DateTime
    {
        return $this->nextFetchAt;
    }

    public function setNextFetchAt(\DateTime $nextFetchAt): void
    {
        $this->nextFetchAt = $nextFetchAt;
    }

    public function getSubscribers(): int
    {
        return $this->subscribers;
    }

    public function setSubscribers(int $subscribers): void
    {
        $this->subscribers = $subscribers;
    }

    public function getConditionalGetLastModified(): ?string
    {
        return $this->conditionalGetLastModified;
    }

    public function setConditionalGetLastModified(?string $conditionalGetLastModified): void
    {
        $this->conditionalGetLastModified = $conditionalGetLastModified;
    }

    public function getConditionalGetEtag(): ?string
    {
        return $this->conditionalGetEtag;
    }

    public function setConditionalGetEtag(?string $conditionalGetEtag): void
    {
        $this->conditionalGetEtag = $conditionalGetEtag;
    }

    public function __construct(string $url)
    {
        $this->url = $url;
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->nextFetchAt = new \DateTime();
    }
}
