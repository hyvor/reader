<?php

namespace App\Entity;

use App\Repository\CollectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection as DoctrineCollection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\CollectionUser;

#[ORM\Entity(repositoryClass: CollectionRepository::class)]
#[ORM\Table(name: 'collections')]
class Collection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private string $name;

    #[ORM\Column(unique: true)]
    private string $slug;

    #[ORM\Column(options: ['default' => false])]
    private bool $isPublic = false;

    #[ORM\Column(name: 'hyvor_user_id')]
    private int $hyvorUserId;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTime $createdAt;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTime $updatedAt;

    /**
     * @var DoctrineCollection<int, Publication>
     */
    #[ORM\ManyToMany(targetEntity: Publication::class, inversedBy: 'collections', cascade: ['persist'])]
    #[ORM\JoinTable(name: 'collection_publications')]
    #[ORM\JoinColumn(name: 'collection_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'publication_id', referencedColumnName: 'id')]
    private DoctrineCollection $publications;

    /**
     * @var DoctrineCollection<int, CollectionUser>
     */
    #[ORM\OneToMany(targetEntity: CollectionUser::class, mappedBy: 'collection', orphanRemoval: true)]
    private DoctrineCollection $collectionUsers;

    public function __construct()
    {
        $this->publications = new ArrayCollection();
        $this->collectionUsers = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return DoctrineCollection<int, Publication>
     */
    public function getPublications(): DoctrineCollection
    {
        return $this->publications;
    }

    public function addPublication(Publication $publication): static
    {
        if (!$this->publications->contains($publication)) {
            $this->publications->add($publication);
            $publication->addCollection($this);
        }

        return $this;
    }

    public function removePublication(Publication $publication): static
    {
        if ($this->publications->removeElement($publication)) {
            $publication->removeCollection($this);
        }

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): static
    {
        $this->isPublic = $isPublic;
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

    /**
     * @return DoctrineCollection<int, CollectionUser>
     */
    public function getCollectionUsers(): DoctrineCollection
    {
        return $this->collectionUsers;
    }

    public function addCollectionUser(CollectionUser $collectionUser): static
    {
        if (!$this->collectionUsers->contains($collectionUser)) {
            $this->collectionUsers->add($collectionUser);
            $collectionUser->setCollection($this);
        }

        return $this;
    }

    public function removeCollectionUser(CollectionUser $collectionUser): static
    {
        if ($this->collectionUsers->removeElement($collectionUser)) {
            if ($collectionUser->getCollection() === $this) {
                $collectionUser->setCollection(null);
            }
        }

        return $this;
    }
}
