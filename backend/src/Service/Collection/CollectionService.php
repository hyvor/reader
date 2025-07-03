<?php

namespace App\Service\Collection;

use App\Entity\Collection;
use App\Entity\CollectionUser;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Auth\AuthUser;

class CollectionService
{

    public function __construct(
        private EntityManagerInterface $em
    )
    {
    }

    /**
     * @return Collection[]
     */
    public function createPrivateCollectionFor(AuthUser $user): Collection
    {
        $firstName = explode(' ', $user->name)[0];
        $collectionName = "{$firstName}'s collection";
        
        $collection = new Collection();
        $collection->setName($collectionName);
        $collection->setSlug($this->generateUniqueSlug($collectionName));
        $collection->setOwnerId($user->id);
        $collection->setPublic(false);
        
        $this->em->persist($collection);
        
        $collectionUser = new CollectionUser();
        $collectionUser->setCollection($collection);
        $collectionUser->setUserId($user->id);
        $collectionUser->setAccess('write');
        
        $this->em->persist($collectionUser);
        $this->em->flush();
        
        return $collection;
    }

    /**
     * @return Collection[]
     */
    public function findAccessibleCollections(AuthUser $user): array
    {
        $qb = $this->em->createQueryBuilder();
        
        return $qb->select('c')
            ->from(Collection::class, 'c')
            ->leftJoin('c.collectionUsers', 'cu')
            ->where('c.public = true OR cu.userId = :userId')
            ->setParameter('userId', $user->id)
            ->getQuery()
            ->getResult();
    }

    public function userCanRead(Collection $collection, AuthUser $user): bool
    {
        if ($collection->isPublic()) {
            return true;
        }
        
        return $this->hasUserAccess($collection, $user, ['read', 'write']);
    }

    public function userCanWrite(Collection $collection, AuthUser $user): bool
    {
        return $this->hasUserAccess($collection, $user, ['write']);
    }

    public function userIsOwner(Collection $collection, AuthUser $user): bool
    {
        return $collection->getOwnerId() === $user->id;
    }

    /**
     * @return Collection[]
     */
    public function getUserCollections(): array
    {
        return $this->em->getRepository(Collection::class)->findAll(); // TODO: find By user
    }

    private function hasUserAccess(Collection $collection, AuthUser $user, array $allowedAccess): bool
    {
        $collectionUser = $this->em->getRepository(CollectionUser::class)
            ->findOneBy([
                'collection' => $collection,
                'userId' => $user->id
            ]);
            
        return $collectionUser && in_array($collectionUser->getAccess(), $allowedAccess);
    }

    private function generateUniqueSlug(string $name): string
    {
        $baseSlug = $this->slugify($name);
        $slug = $baseSlug;
        $counter = 1;
        
        while ($this->slugExists($slug)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    private function slugify(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }

    private function slugExists(string $slug): bool
    {
        return $this->em->getRepository(Collection::class)
            ->findOneBy(['slug' => $slug]) !== null;
    }
}