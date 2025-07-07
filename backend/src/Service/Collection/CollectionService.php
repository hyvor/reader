<?php

namespace App\Service\Collection;

use App\Entity\Collection;
use App\Entity\CollectionUser;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Auth\AuthUser;
<<<<<<< Updated upstream
=======
use Symfony\Component\String\Slugger\AsciiSlugger;
>>>>>>> Stashed changes

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
<<<<<<< Updated upstream
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
=======
    public function getUserCollections(int $hyvorUserId): array
>>>>>>> Stashed changes
    {
        $ownedCollections = $this->em->getRepository(Collection::class)->findBy([
            'hyvorUserId' => $hyvorUserId
        ]);

        $collectionUsers = $this->em->getRepository(CollectionUser::class)->findBy([
            'hyvorUserId' => $hyvorUserId
        ]);

        $accessibleCollections = array_map(
            fn(CollectionUser $cu) => $cu->getCollection(),
            $collectionUsers
        );

        return array_merge($ownedCollections, $accessibleCollections);
    }

<<<<<<< Updated upstream
    private function hasUserAccess(Collection $collection, AuthUser $user, array $allowedAccess): bool
    {
        $collectionUser = $this->em->getRepository(CollectionUser::class)
            ->findOneBy([
                'collection' => $collection,
                'userId' => $user->id
            ]);
            
        return $collectionUser && in_array($collectionUser->getAccess(), $allowedAccess);
=======
    public function ensureUserHasCollection(AuthUser $user): Collection
    {
        $existingCollections = $this->em->getRepository(Collection::class)->findBy([
            'hyvorUserId' => $user->id
        ]);

        if (!empty($existingCollections)) {
            return $existingCollections[0];
        }

        return $this->createCollection($user->id, $user->name . "'s collection", false);
    }

    public function createCollection(int $hyvorUserId, string $name, bool $isPublic = false): Collection
    {
        $collection = new Collection();
        $collection->setName($name);
        $collection->setSlug($this->generateUniqueSlug($name));
        $collection->setIsPublic($isPublic);
        $collection->setHyvorUserId($hyvorUserId);

        $collectionUser = new CollectionUser();
        $collectionUser->setHyvorUserId($hyvorUserId);
        $collectionUser->setCollection($collection);
        $collectionUser->setWriteAccess(true);

        $this->em->persist($collection);
        $this->em->persist($collectionUser);
        $this->em->flush();

        return $collection;
    }

    public function joinCollection(int $hyvorUserId, string $collectionSlug): CollectionUser
    {
        $collection = $this->em->getRepository(Collection::class)->findOneBy([
            'slug' => $collectionSlug
        ]);

        if (!$collection) {
            throw new \InvalidArgumentException('Collection not found');
        }

        if (!$collection->isPublic()) {
            throw new \InvalidArgumentException('Cannot join private collection');
        }

        if ($collection->getHyvorUserId() === $hyvorUserId) {
            throw new \InvalidArgumentException('Cannot join a collection you own');
        }

        $existingAccess = $this->em->getRepository(CollectionUser::class)->findUserCollectionAccess(
            $hyvorUserId,
            $collection->getId()
        );

        if ($existingAccess) {
            throw new \InvalidArgumentException('User already has access to this collection');
        }

        $collectionUser = new CollectionUser();
        $collectionUser->setHyvorUserId($hyvorUserId);
        $collectionUser->setCollection($collection);
        $collectionUser->setWriteAccess(false);

        $this->em->persist($collectionUser);
        $this->em->flush();

        return $collectionUser;
    }

    public function leaveCollection(int $hyvorUserId, string $collectionSlug): void
    {
        $collection = $this->em->getRepository(Collection::class)->findOneBy([
            'slug' => $collectionSlug
        ]);

        if (!$collection) {
            throw new \InvalidArgumentException('Collection not found');
        }

        if ($collection->getHyvorUserId() === $hyvorUserId) {
            throw new \InvalidArgumentException('Cannot leave a collection you own');
        }

        $collectionUser = $this->em->getRepository(CollectionUser::class)->findUserCollectionAccess(
            $hyvorUserId,
            $collection->getId()
        );

        if (!$collectionUser) {
            throw new \InvalidArgumentException('User does not have access to this collection');
        }

        $this->em->remove($collectionUser);
        $this->em->flush();
    }

    public function findBySlug(string $slug): ?Collection
    {
        return $this->em->getRepository(Collection::class)->findOneBy(['slug' => $slug]);
    }

    public function hasUserAccess(int $hyvorUserId, Collection $collection): bool
    {
        if ($collection->getHyvorUserId() === $hyvorUserId) {
            return true;
        }

        if ($collection->isPublic()) {
            return true;
        }

        $collectionUser = $this->em->getRepository(CollectionUser::class)->findUserCollectionAccess(
            $hyvorUserId,
            $collection->getId()
        );

        return $collectionUser !== null;
    }

    public function hasUserWriteAccess(int $hyvorUserId, Collection $collection): bool
    {
        if ($collection->getHyvorUserId() === $hyvorUserId) {
            return true;
        }

        $collectionUser = $this->em->getRepository(CollectionUser::class)->findUserCollectionAccess(
            $hyvorUserId,
            $collection->getId()
        );

        return $collectionUser && $collectionUser->hasWriteAccess();
>>>>>>> Stashed changes
    }

    private function generateUniqueSlug(string $name): string
    {
<<<<<<< Updated upstream
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
=======
        $slugger = new AsciiSlugger();
        $baseSlug = $slugger->slug($name)->lower()->toString();
        
        $slug = $baseSlug;
        $counter = 1;

        while ($this->em->getRepository(Collection::class)->findOneBy(['slug' => $slug])) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
>>>>>>> Stashed changes
}