<?php

namespace App\Entity;

use App\Repository\CollectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection as DoctrineCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use App\Entity\CollectionUser;

#[ORM\Entity(repositoryClass: CollectionRepository::class)]
#[ORM\Table(name: 'collections')]
class Collection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(unique: true)]
    private string $uuid;

    #[ORM\Column(unique: true)]
    private string $slug;

    #[ORM\Column(name: 'is_public', options: ['default' => false])]
    private bool $public = false;

    #[ORM\Column(name: 'owner_id')]
    private int $ownerId;

    #[ORM\Column]
    private string $name;

    #[ORM\Column(unique: true)]
    private string $slug;

    #[ORM\Column(options: ['default' => false])]
    private bool $isPublic = false;

    #[ORM\Column(name: 'hyvor_user_id')]
    private int $hyvorUserId;

    /**
     * @var DoctrineCollection<int, Publication>
     */
    #[ORM\OneToMany(targetEntity: Publication::class, mappedBy: 'collection', orphanRemoval: true)]
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
        $this->uuid = Uuid::v4();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUuid(): string
    {
        return $this->uuid;
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
        return $this->public;
    }

    public function setPublic(bool $public): static
    {
        $this->public = $public;
        return $this;
    }

    public function getOwnerId(): int
    {
        return $this->ownerId;
    }

    public function setOwnerId(int $ownerId): static
    {
        $this->ownerId = $ownerId;
        return $this;
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
            $publication->setCollection($this);
        }

        return $this;
    }

    public function removePublication(Publication $publication): static
    {
        if ($this->publications->removeElement($publication)) {
            if ($publication->getCollection() === $this) {
                $publication->setCollection(null);
            }
        }

        return $this;
    }

<<<<<<< Updated upstream
=======
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

>>>>>>> Stashed changes
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
