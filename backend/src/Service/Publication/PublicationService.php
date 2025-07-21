<?php

namespace App\Service\Publication;

use App\Entity\Publication;
use App\Entity\Collection;
use App\Api\App\Object\PublicationObject;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

class PublicationService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function findBySlug(string $slug): ?Publication
    {
        return $this->em->getRepository(Publication::class)->findOneBy(['slug' => $slug]);
    }

    /**
     * @return PublicationObject[]
     */
    public function getPublicationsFromCollection(Collection $collection): array
    {
        $publications = [];
        foreach ($collection->getPublications() as $publication) {
            $publications[] = new PublicationObject($publication);
        }

        return $publications;
    }

    public function createPublication(Collection $collection, string $url, ?string $title = null, ?string $description = null): Publication
    {
        $publication = new Publication();
        $publication->setUrl($url);
        $publication->setTitle($title);
        $publication->setDescription($description);
        $publication->addCollection($collection);
        $publication->setSlug($this->generateUniqueSlug($title ?: $url));

        $this->em->persist($publication);
        $this->em->flush();

        return $publication;
    }

    private function generateUniqueSlug(string $text): string
    {
        $slugger = new AsciiSlugger();
        $baseSlug = $slugger->slug($text)->lower()->toString();
        $slug = $baseSlug;
        $counter = 1;

        while ($this->em->getRepository(Publication::class)->findOneBy(['slug' => $slug])) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
} 