<?php

namespace App\Entity;

use App\Repository\CollectionUserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CollectionUserRepository::class)]
#[ORM\Table(name: 'collection_users')]
#[ORM\UniqueConstraint(columns: ['collection_id', 'hyvor_user_id'])]
class CollectionUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Collection::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Collection $collection;

    #[ORM\Column(name: 'hyvor_user_id', type: 'bigint')]
    private int $hyvorUserId;

    #[ORM\Column(name: 'write_access', type: 'boolean', options: ['default' => false])]
    private bool $writeAccess = false;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCollection(): Collection
    {
        return $this->collection;
    }

    public function setCollection(Collection $collection): static
    {
        $this->collection = $collection;
        return $this;
    }

    public function getHyvorUserId(): int
    {
        return $this->hyvorUserId;
    }

    public function setHyvorUserId(int $hyvorUserId): static
    {
        $this->hyvorUserId = $hyvorUserId;
        return $this;
    }

    public function hasWriteAccess(): bool
    {
        return $this->writeAccess;
    }

    public function setWriteAccess(bool $writeAccess): static
    {
        $this->writeAccess = $writeAccess;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
} 
