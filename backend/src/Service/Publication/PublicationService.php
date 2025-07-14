<?php

namespace App\Service\Publication;

use App\Entity\Publication;
use App\Entity\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Uid\Uuid;

class PublicationService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function findByUuid(string $uuid): ?Publication
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            return $this->em->getRepository(Publication::class)->findOneBy(['uuid' => $uuidObject]);
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    public function createPublication(Collection $collection, string $url, ?string $title = null, ?string $description = null): Publication
    {
        $publication = new Publication();
        $publication->setUrl($url);
        $publication->setTitle($title);
        $publication->setDescription($description);
        $publication->setCollection($collection);
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