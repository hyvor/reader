<?php

namespace App\Repository;

use App\Entity\Publication;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Publication>
 */
class PublicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Publication::class);
    }

    /**
     * @param \DateTime $before
     * @return Publication[]
     */
    public function findDueForFetching(\DateTime $before): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.nextFetchAt <= :before')
            ->setParameter('before', $before)
            ->orderBy('p.nextFetchAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
