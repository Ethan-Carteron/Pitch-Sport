<?php

namespace App\Repository;

use App\Entity\Club;
use App\Entity\Player;
use DateTime;
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
        $date = new DateTime('-30 days');

        return $this->createQueryBuilder('player')
            ->innerJoin('player.workloads', 'workload')
            // ->andWhere('workload.createdDate >= :date')
            // ->setParameter('date', $date)
            ->groupBy('player.id')
            ->orderBy('player.score', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPlayerByNameInClub(string $name, Club $club): ?Player
    {
        return $this->createQueryBuilder('player')
            ->andWhere('player.club = :club AND player.name = :name')
            ->setParameter('club', $club)
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array<int, int> Indexed by alert level (0=green, 1=orange, 2=red)
     */
    public function countByAlertLevel(): array
    {
        $results = $this->createQueryBuilder('p')
            ->select('p.score, COUNT(p.id) as total')
            ->andWhere('p.isDeleted = false')
            ->groupBy('p.score')
            ->getQuery()
            ->getResult();

        $counts = [0 => 0, 1 => 0, 2 => 0];
        foreach ($results as $row) {
            $score = $row['score'];
            if ($score !== null && isset($counts[$score])) {
                $counts[$score] = (int) $row['total'];
            }
        }

        return $counts;
    }
}
