<?php

namespace App\Entity;

use App\Enum\FetchStatusEnum;
use App\Repository\PublicationFetchRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PublicationFetchRepository::class)]
#[ORM\Table(name: 'publication_fetches')]
class PublicationFetch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'uuid', unique: true)]
    private ?Uuid $uuid = null;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTime $createdAt;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTime $updatedAt;

    #[ORM\ManyToOne(targetEntity: Publication::class, inversedBy: 'fetches')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Publication $publication;

    #[ORM\Column(type: 'string', enumType: FetchStatusEnum::class)]
    private FetchStatusEnum $status;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $statusCode = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $error = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $errorPrivate = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $newItemsCount = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $updatedItemsCount = 0;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $latencyMs = null;

    public function __construct()
    {
        $this->uuid = Uuid::v4();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->status = FetchStatusEnum::PENDING;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getPublication(): Publication
    {
        return $this->publication;
    }

    public function setPublication(Publication $publication): static
    {
        $this->publication = $publication;
        return $this;
    }

    public function getStatus(): FetchStatusEnum
    {
        return $this->status;
    }

    public function setStatus(FetchStatusEnum $status): static
    {
        $this->status = $status;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function setStatusCode(?int $statusCode): static
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function setError(?string $error): static
    {
        $this->error = $error;
        return $this;
    }

    public function getErrorPrivate(): ?string
    {
        return $this->errorPrivate;
    }

    public function setErrorPrivate(?string $errorPrivate): static
    {
        $this->errorPrivate = $errorPrivate;
        return $this;
    }

    public function getNewItemsCount(): int
    {
        return $this->newItemsCount;
    }

    public function setNewItemsCount(int $newItemsCount): static
    {
        $this->newItemsCount = $newItemsCount;
        return $this;
    }

    public function getUpdatedItemsCount(): int
    {
        return $this->updatedItemsCount;
    }

    public function setUpdatedItemsCount(int $updatedItemsCount): static
    {
        $this->updatedItemsCount = $updatedItemsCount;
        return $this;
    }

    public function getLatencyMs(): ?int
    {
        return $this->latencyMs;
    }

    public function setLatencyMs(?int $latencyMs): static
    {
        $this->latencyMs = $latencyMs;
        return $this;
    }

    public function markAsCompleted(): static
    {
        $this->status = FetchStatusEnum::COMPLETED;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function markAsFailed(string $error = null, string $errorPrivate = null): static
    {
        $this->status = FetchStatusEnum::FAILED;
        $this->error = $error;
        $this->errorPrivate = $errorPrivate;
        $this->updatedAt = new \DateTime();
        return $this;
    }
} 