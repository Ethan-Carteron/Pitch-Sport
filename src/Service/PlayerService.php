<?php

namespace App\Service;

use App\Entity\Club;
use App\Entity\Player;
use App\Entity\User;
use App\Repository\PlayerRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

readonly class PlayerService
{
    public function __construct(
        private PlayerRepository       $playerRepository,
        private EntityManagerInterface $entityManager
    )
    {
    }

    public function findActivePlayers(): array
    {
        return $this->playerRepository->findActivePlayers();
    }

    public function findPlayerByNameInClub(string $name, Club $club): ?Player
    {
        return $this->playerRepository->findOneBy(['name' => $name, 'club' => $club]);
    }

    public function createPlayer(string $name, Club $club, User $creator): Player
    {
        $player = new Player()
            ->setName($name)
            ->setClub($club)
            ->setCreatedBy($creator)
            ->setCreatedDate(new DateTime());

        $this->entityManager->persist($player);

        return $player;
    }
}
