<?php

namespace App\Entity;

use App\Repository\PublicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection as DoctrineCollection;
use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity(repositoryClass: PublicationRepository::class)]
#[ORM\Table(name: 'publications')]
class Publication
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(type: 'string', unique: true)]
    private string $url;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'integer', options: ['default' => 60])]
    private int $interval = 60;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastFetchedAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $nextFetchAt;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $subscribers = 0;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $conditionalGetLastModified = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $conditionalGetEtag = null;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    private string $slug;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isFetching = false;

    /**
     * @var DoctrineCollection<int, Item>
     */
    #[ORM\OneToMany(targetEntity: Item::class, mappedBy: 'publication', orphanRemoval: true)]
    private DoctrineCollection $items;

    /**
     * @var DoctrineCollection<int, Collection>
     */
    #[ORM\ManyToMany(targetEntity: Collection::class, mappedBy: 'publications', cascade: ['persist'])]
    private DoctrineCollection $collections;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->collections = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->nextFetchAt = new \DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getSubscribers(): int
    {
        return $this->subscribers;
    }

    public function setSubscribers(int $subscribers): static
    {
        $this->subscribers = $subscribers;
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

    public function getInterval(): int
    {
        return $this->interval;
    }

    public function setInterval(int $interval): static
    {
        $this->interval = $interval;
        return $this;
    }

    public function getLastFetchedAt(): ?\DateTimeImmutable
    {
        return $this->lastFetchedAt;
    }

    public function setLastFetchedAt(?\DateTimeImmutable $lastFetchedAt): static
    {
        $this->lastFetchedAt = $lastFetchedAt;
        return $this;
    }

    public function getNextFetchAt(): \DateTimeImmutable
    {
        return $this->nextFetchAt;
    }

    public function setNextFetchAt(\DateTimeImmutable $nextFetchAt): static
    {
        $this->nextFetchAt = $nextFetchAt;
        return $this;
    }

    public function getConditionalGetLastModified(): ?string
    {
        return $this->conditionalGetLastModified;
    }

    public function setConditionalGetLastModified(?string $conditionalGetLastModified): static
    {
        $this->conditionalGetLastModified = $conditionalGetLastModified;
        return $this;
    }

    public function getConditionalGetEtag(): ?string
    {
        return $this->conditionalGetEtag;
    }

    public function setConditionalGetEtag(?string $conditionalGetEtag): static
    {
        $this->conditionalGetEtag = $conditionalGetEtag;
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

    public function getIsFetching(): bool
    {
        return $this->isFetching;
    }

    public function setIsFetching(bool $isFetching): static
    {
        $this->isFetching = $isFetching;
        return $this;
    }

    /**
     * @return DoctrineCollection<int, Item>
     */
    public function getItems(): DoctrineCollection
    {
        return $this->items;
    }

    public function addItem(Item $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setPublication($this);
        }

        return $this;
    }

    public function removeItem(Item $item): static
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getPublication() === $this) {
                $item->setPublication(null);
            }
        }

        return $this;
    }

    /**
     * @return DoctrineCollection<int, Collection>
     */
    public function getCollections(): DoctrineCollection
    {
        return $this->collections;
    }

    public function addCollection(Collection $collection): static
    {
        if (!$this->collections->contains($collection)) {
            $this->collections->add($collection);
            $collection->addPublication($this);
        }

        return $this;
    }

    public function removeCollection(Collection $collection): static
    {
        if ($this->collections->removeElement($collection)) {
            $collection->removePublication($this);
        }

        return $this;
    }

}
