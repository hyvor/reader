<?php

namespace App\Entity;

use App\Repository\FeedListRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FeedListRepository::class)]
class FeedList
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private string $name;

    /**
     * @var Collection<int, Feed>
     */
    #[ORM\OneToMany(targetEntity: Feed::class, mappedBy: 'feed_list', orphanRemoval: true)]
    private Collection $feeds;

    public function __construct()
    {
        $this->feeds = new ArrayCollection();
    }

    /**
     * @return Collection<int, Feed>
     */
    public function getFeeds(): Collection
    {
        return $this->feeds;
    }

    public function addFeed(Feed $feed): static
    {
        if (!$this->feeds->contains($feed)) {
            $this->feeds->add($feed);
            $feed->setFeedList($this);
        }

        return $this;
    }

    public function removeFeed(Feed $feed): static
    {
        if ($this->feeds->removeElement($feed)) {
            // set the owning side to null (unless already changed)
            if ($feed->getFeedList() === $this) {
                $feed->setFeedList(null);
            }
        }

        return $this;
    }
}
