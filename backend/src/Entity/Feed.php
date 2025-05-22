<?php

namespace App\Entity;

use App\Repository\FeedRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    /**
     * @var Collection<int, Item>
     */
    #[ORM\OneToMany(targetEntity: Item::class, mappedBy: 'feed', orphanRemoval: true)]
    private Collection $items;

    #[ORM\ManyToOne(inversedBy: 'feeds')]
    #[ORM\JoinColumn(nullable: false)]
    private ?FeedList $feed_list = null;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    /**
     * @return Collection<int, Item>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(Item $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setFeed($this);
        }

        return $this;
    }

    public function removeItem(Item $item): static
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getFeed() === $this) {
                $item->setFeed(null);
            }
        }

        return $this;
    }

    public function getFeedList(): ?FeedList
    {
        return $this->feed_list;
    }

    public function setFeedList(?FeedList $feed_list): static
    {
        $this->feed_list = $feed_list;

        return $this;
    }

}
