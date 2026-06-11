<?php

namespace App\Service;

use App\Entity\Player;
use App\Repository\PlayerRepository;
use App\Repository\WorkloadRepository;
use DateTimeImmutable;
use DateTimeInterface;

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

    private function getRecentCharges(Player $player, int $limit, ?DateTimeInterface $date = null): array
    {
        $referenceDate = $date ?? new DateTimeImmutable();
        $workloads = $this->workloadRepository->findRecentWorkloads($player, $limit, $referenceDate);

        $charges = [];
        foreach ($workloads as $workload) {
            $charges[] = $workload->getCharge();
        }

        return $charges;
    }

    private function averageCharge(Player $player, int $limit, ?DateTimeInterface $date = null): ?float
    {
        $charges = $this->getRecentCharges($player, $limit, $date);

        if (empty($charges)) {
            return null;
        }

        return array_sum($charges) / count($charges);
    }

    public function calculAcwr(Player $player, ?DateTimeInterface $date = null): ?float
    {
        $acuteLoad = $this->averageCharge($player, 7, $date);
        $chronicLoad = $this->averageCharge($player, 28, $date);

        if (!$chronicLoad) {
            return null;
        }

        $acwr = $acuteLoad / $chronicLoad;
        return round($acwr, 2);
    }

    public function calculFosterMonotony(Player $player, ?DateTimeInterface $date = null): ?float
    {
        $charges = $this->getRecentCharges($player, 7, $date);
        $count = count($charges);

        if ($count < 2) {
            return null;
        }

        $average = array_sum($charges) / $count;
        $variance = 0.0;

        foreach ($charges as $charge) {
            $variance += pow($charge - $average, 2);
        }
        $variance /= $count;

        $stdDeviation = sqrt($variance);

        if ($stdDeviation == 0) {
            return null;
        }

        return round($average / $stdDeviation, 2);
    }

    public function getFosterHistory(Player $player): array
    {
        $workloads = $this->workloadRepository->findBy(
            ['player' => $player, 'isDeleted' => false],
            ['createdDate' => 'DESC'],
            15
        );

        $history = [];
        $workloads = array_reverse($workloads);

        foreach ($workloads as $w) {
            $foster = $this->calculFosterMonotony($player, $w->getCreatedDate());
            if ($foster !== null) {
                $history[] = [
                    'date' => $w->getCreatedDate()->format('d/m'),
                    'value' => $foster,
                    'level' => $this->getFosterAlertLevel($foster),
                ];
            }
        }

        return $history;
    }

    public function getFosterAlertLevel(?float $foster): ?int
    {
        if ($foster === null) {
            return null;
        }

        if ($foster >= 2.5) {
            return self::ALERT_RED;
        }

        if ($foster >= 2.0) {
            return self::ALERT_ORANGE;
        }

        return self::ALERT_GREEN;
    }

    private function getRecentVmax(Player $player, int $limit, ?DateTimeInterface $date = null): array
    {
        $referenceDate = $date ?? new DateTimeImmutable();
        $workloads = $this->workloadRepository->findRecentWorkloads($player, $limit, $referenceDate);

        $vmaxList = [];
        foreach ($workloads as $workload) {
            $vmax = $workload->getMaxSpeed();
            if ($vmax !== null) {
                $vmaxList[] = $vmax;
            }
        }

        return $vmaxList;
    }

    public function calculVmaxDrop(Player $player, ?DateTimeInterface $date = null): ?float
    {
        $vmaxList = $this->getRecentVmax($player, 28, $date);

        if (empty($vmaxList)) {
            return null;
        }

        $lastVmax = $vmaxList[0];
        $averageVmax = array_sum($vmaxList) / count($vmaxList);

        if ($averageVmax == 0) {
            return null;
        }

        $drop = (($averageVmax - $lastVmax) / $averageVmax) * 100;

        return round($drop, 2);
    }

    public function getVmaxDropHistory(Player $player): array
    {
        $workloads = $this->workloadRepository->findBy(
            ['player' => $player, 'isDeleted' => false],
            ['createdDate' => 'DESC'],
            15
        );

        $history = [];
        $workloads = array_reverse($workloads);

        foreach ($workloads as $w) {
            $drop = $this->calculVmaxDrop($player, $w->getCreatedDate());
            if ($drop !== null) {
                $history[] = [
                    'date' => $w->getCreatedDate()->format('d/m'),
                    'value' => $drop,
                    'level' => $this->getVmaxDropAlertLevel($drop),
                ];
            }
        }

        return $history;
    }

    public function getVmaxDropAlertLevel(?float $drop): ?int
    {
        if ($drop === null) {
            return null;
        }

        if ($drop >= 10) {
            return self::ALERT_RED;
        }

        if ($drop >= 5) {
            return self::ALERT_ORANGE;
        }

        return self::ALERT_GREEN;
    }

    public function getDistanceHistory(Player $player): array
    {
        $workloads = $this->workloadRepository->findBy(
            ['player' => $player, 'isDeleted' => false],
            ['createdDate' => 'DESC'],
            15
        );

        $history = [];
        $workloads = array_reverse($workloads);

        foreach ($workloads as $w) {
            $distance = $w->getTotalDistance();
            if ($distance !== null) {
                $history[] = [
                    'date' => $w->getCreatedDate()->format('d/m'),
                    'value' => $distance,
                    'level' => self::ALERT_GREEN,
                ];
            }
        }

        return $history;
    }

    public function getAcwrHistory(Player $player): array
    {
        $workloads = $this->workloadRepository->findBy(
            ['player' => $player, 'isDeleted' => false],
            ['createdDate' => 'DESC'],
            15
        );

        $history = [];
        $workloads = array_reverse($workloads);

        foreach ($workloads as $w) {
            $acwr = $this->calculAcwr($player, $w->getCreatedDate());
            if ($acwr !== null) {
                $history[] = [
                    'date' => $w->getCreatedDate()->format('d/m'),
                    'value' => $acwr,
                    'level' => $this->getAlertLevel($acwr),
                ];
            }
        }

        return $history;
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
        $players = $this->playerRepository->findBy(['isDeleted' => false]);

        $counts = [
            self::ALERT_GREEN => 0,
            self::ALERT_ORANGE => 0,
            self::ALERT_RED => 0,
        ];

        foreach ($players as $player) {
            $score = $player->getScore();
            if ($score !== null && isset($counts[$score])) {
                $counts[$score]++;
            }
        }

        return [
            'green' => $counts[self::ALERT_GREEN],
            'orange' => $counts[self::ALERT_ORANGE],
            'red' => $counts[self::ALERT_RED],
        ];
    }
}
