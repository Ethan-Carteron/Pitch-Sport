<?php

namespace App\Repository;

use App\Entity\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Player>
 */
class PlayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    /**
     * @return Player[]
     */
    public function findActivePlayers(): array
    {
        return $this->createQueryBuilder('player')
            ->innerJoin('player.workloads', 'workload')
            ->groupBy('player.id')
            ->orderBy('player.score', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
