<?php

namespace App\Service\Collection;

use App\Entity\Collection;
use App\Entity\CollectionUser;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Auth\AuthUser;
use Symfony\Component\String\Slugger\AsciiSlugger;

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
    public function getUserCollections(int $hyvorUserId): array
    {
        $collectionUsers = $this->em->getRepository(CollectionUser::class)->findBy([
            'hyvorUserId' => $hyvorUserId
        ]);

        return array_map(
            fn(CollectionUser $cu) => $cu->getCollection(),
            $collectionUsers
        );
    }

    public function ensureUserHasDefaultCollection(AuthUser $user): void
    {
        $existingCollectionsCount = $this->em->getRepository(CollectionUser::class)->count([
            'hyvorUserId' => $user->id,
        ]);

        if ($existingCollectionsCount === 0) {
            $this->createCollection($user->id, $user->name . "'s collection", false);
        }
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

    /*public function joinCollection(int $hyvorUserId, string $collectionSlug): CollectionUser
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
    }*/

    /*public function leaveCollection(int $hyvorUserId, string $collectionSlug): void
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
    }*/

    public function findBySlug(string $slug): ?Collection
    {
        return $this->em->getRepository(Collection::class)->findOneBy(['slug' => $slug]);
    }

    public function hasUserReadAccess(int $hyvorUserId, Collection $collection): bool
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
    }

    /*public function deleteCollection(int $hyvorUserId, string $collectionSlug): void
    {
        $collection = $this->em->getRepository(Collection::class)->findOneBy([
            'slug' => $collectionSlug
        ]);

        if (!$collection) {
            throw new \InvalidArgumentException('Collection not found');
        }

        if ($collection->getHyvorUserId() !== $hyvorUserId) {
            throw new \InvalidArgumentException('Only the collection owner can delete the collection');
        }

        $this->em->remove($collection);
        $this->em->flush();
    }*/

    private function generateUniqueSlug(string $name): string
    {
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
}