<?php

namespace App\Service;

use App\Repository\PlayerRepository;

readonly class PlayerService
{
    public function __construct(
        private PlayerRepository $playerRepository
    )
    {
    }
    public function findActivePlayers() {
        return $this->playerRepository->findActivePlayers();
    }
}
