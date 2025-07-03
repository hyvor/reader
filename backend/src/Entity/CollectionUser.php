<?php

namespace App\Entity;

use App\Repository\CollectionUserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CollectionUserRepository::class)]
#[ORM\Table(name: 'collection_users')]
#[ORM\UniqueConstraint(name: 'uniq_collection_user', columns: ['collection_id', 'user_id'])]
class CollectionUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Collection::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Collection $collection;

    #[ORM\Column(name: 'user_id', type: 'integer')]
    private int $userId;

    #[ORM\Column(type: 'string', length: 5)]
    private string $access; 

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

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): static
    {
        $this->userId = $userId;
        return $this;
    }

    public function getAccess(): string
    {
        return $this->access;
    }

    public function setAccess(string $access): static
    {
        $this->access = $access;
        return $this;
    }
} 