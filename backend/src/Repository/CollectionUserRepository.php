<?php

namespace App\Repository;

use App\Entity\CollectionUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CollectionUser>
 */
class CollectionUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CollectionUser::class);
    }

    /**
     * @return CollectionUser|null
     */
    public function findUserCollectionAccess(int $hyvorUserId, int $collectionId): ?CollectionUser
    {
        $result = $this->createQueryBuilder('cu')
            ->andWhere('cu.hyvorUserId = :hyvorUserId')
            ->andWhere('cu.collection = :collectionId')
            ->setParameter('hyvorUserId', $hyvorUserId)
            ->setParameter('collectionId', $collectionId)
            ->getQuery()
            ->getOneOrNullResult();

        assert($result instanceof CollectionUser || $result === null);
        return $result;
    }
}
