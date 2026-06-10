<?php

namespace App\Repository;

use App\Entity\Player;
use App\Entity\Workload;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Workload>
 */
class WorkloadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Workload::class);
    }

    /**
     * @return Workload[]
     */
    public function findRecentWorkloads(Player $player, int $limit, DateTimeInterface $beforeDate): array
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.player = :player')
            ->andWhere('w.isDeleted = false')
            ->andWhere('w.createdDate <= :date')
            ->setParameter('player', $player)
            ->setParameter('date', $beforeDate->format('Y-m-d'))
            ->orderBy('w.createdDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
