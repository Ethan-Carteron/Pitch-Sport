<?php

namespace App\Service;

use App\Entity\Player;
use App\Repository\PlayerRepository;
use App\Repository\WorkloadRepository;

readonly class CalculService
{
    public const int ALERT_GREEN = 0;
    public const int ALERT_ORANGE = 1;
    public const int ALERT_RED = 2;

    public function __construct(
        private WorkloadRepository $workloadRepository,
        private PlayerRepository $playerRepository
    ) {
    }

    public function calculAcwr(Player $player): ?float
    {
        $acuteLoad = $this->workloadRepository->averageCharge($player, 7);
        $chronicLoad = $this->workloadRepository->averageCharge($player, 28);

        if (!$chronicLoad) {
            return null;
        }

        $acwr = $acuteLoad / $chronicLoad;
        return round($acwr, 2);
    }

    public function getAlertLevel(?float $acwr): ?int
    {
        if ($acwr === null) {
            return null;
        }

        if ($acwr <= 0.7 || $acwr >= 1.5) {
            return self::ALERT_RED;
        }

        if ($acwr < 0.8 || $acwr > 1.3) {
            return self::ALERT_ORANGE;
        }

        return self::ALERT_GREEN;
    }

    public function updatePlayerAlertLevel(Player $player): ?float
    {
        $acwr = $this->calculAcwr($player);
        $alertLevel = $this->getAlertLevel($acwr);

        $player->setScore($alertLevel ?? self::ALERT_GREEN);

        return $acwr;
    }

    /**
     * @return array{green: int, orange: int, red: int}
     */
    public function getAlertCounts(): array
    {
        $counts = $this->playerRepository->countByAlertLevel();

        return [
            'green' => $counts[self::ALERT_GREEN],
            'orange' => $counts[self::ALERT_ORANGE],
            'red' => $counts[self::ALERT_RED],
        ];
    }
}
