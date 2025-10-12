<?php

namespace App\Service\Publication;

use App\Entity\Publication;
use App\Entity\Collection;
use App\Api\App\Object\PublicationObject;
use App\Service\Parser\Types\Feed;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;
use App\Service\Fetch\FetchService;

class PublicationService
{
    public function __construct(
        private EntityManagerInterface $em,
        private FetchService $fetchService,
    )
    {
    }

    public function findBySlug(string $slug): ?Publication
    {
        return $this->em->getRepository(Publication::class)->findOneBy(['slug' => $slug]);
    }

    public function findByUrl(string $url): ?Publication
    {
        return $this->em->getRepository(Publication::class)->findOneBy(['url' => $url]);
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

    /**
     * @param array{
     *     final_url: string,
     *     feed: Feed,
     *     title: string,
     *     headers: array<string, array<int, string>>
     * } $inspection
     */
    public function addPublication(Collection $collection, array $inspection, bool $flush = true): Publication
    {
        $url = $inspection['final_url'];
        $feed = $inspection['feed'];
        $title = $inspection['title'];
        /** @var array<string, array<int, string>> $headers */
        $headers = $inspection['headers'];

        $publication = new Publication();
        $publication->setUrl($url);
        $publication->addCollection($collection);
        $publication->setSlug($this->generateUniqueSlug($title ?: $url));

        if (isset($headers['etag'][0])) {
            $publication->setConditionalGetEtag($headers['etag'][0]);
        }
        if (isset($headers['last-modified'][0])) {
            $publication->setConditionalGetLastModified($headers['last-modified'][0]);
        }

        $this->fetchService->processItems($publication, $feed);
        if ($title && $publication->getTitle() !== $title) {
            $publication->setTitle($feed->title);
        }
        if ($feed->description && $publication->getDescription() !== $feed->description) {
            $publication->setDescription($feed->description);
        }
        $publication->setLastFetchedAt(new \DateTimeImmutable());
        $this->fetchService->updateNextFetchTime($publication);

        $this->em->persist($publication);
        if ($flush) {
            $this->em->flush();
        }

        return $publication;
    }

    public function attachToCollectionIfMissing(Publication $publication, Collection $collection, bool $flush = true): bool
    {
        if (!$publication->getCollections()->contains($collection)) {
            $publication->addCollection($collection);
            if ($flush) {
                $this->em->flush();
            }
            return true;
        }
        return false;
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