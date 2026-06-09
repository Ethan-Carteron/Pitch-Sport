<?php

namespace App\Repository;

use App\Entity\Player;
use App\Entity\Workload;
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
     * Moyenne de charge (distance + 10*accélération + 8*décélération)
     * sur les N dernières séances d'un joueur.
     */
    public function averageCharge(Player $player, int $limit): ?float
    {
        $recentIds = $this->createQueryBuilder('w')
            ->select('w.id')
            ->andWhere('w.player = :player')
            ->andWhere('w.isDeleted = false')
            ->setParameter('player', $player)
            ->orderBy('w.createdDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getSingleColumnResult();

        if (empty($recentIds)) {
            return null;
        }

        return $this->createQueryBuilder('w')
            ->select('AVG(w.totalDistance + 10 * COALESCE(w.acceleration, 0) + 8 * COALESCE(w.deceleration, 0))')
            ->andWhere('w.id IN (:ids)')
            ->setParameter('ids', $recentIds)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
