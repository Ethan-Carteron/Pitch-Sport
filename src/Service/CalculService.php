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
        $nombreDeSeances = count($charges);

        if ($nombreDeSeances < 2) {
            return null;
        }

        // Étape 1 : Calcul de la charge moyenne de la semaine
        $sommeDesCharges = array_sum($charges);
        $chargeMoyenne = $sommeDesCharges / $nombreDeSeances;

        // Étape 2 : Calcul de l'écart type des charges
        $sommeDesEcartsAuCarre = 0.0;
        foreach ($charges as $charge) {
            $sommeDesEcartsAuCarre += pow($charge - $chargeMoyenne, 2);
        }
        $variance = $sommeDesEcartsAuCarre / $nombreDeSeances;
        $ecartType = sqrt($variance);

        // Étape 3 : Calcul de la monotonie (Moyenne divisée par l'écart type)
        if ($ecartType == 0) {
            return null; // Éviter la division par zéro
        }

        $monotonie = $chargeMoyenne / $ecartType;

        return round($monotonie, 2);
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
        return match (true) {
            $foster === null => null,
            $foster >= 2.5 => self::ALERT_RED,
            $foster >= 2.0 => self::ALERT_ORANGE,
            default => self::ALERT_GREEN,
        };
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
        $vmaxList28 = $this->getRecentVmax($player, 28, $date);
        $vmaxList7 = $this->getRecentVmax($player, 7, $date);

        if (empty($vmaxList28) || empty($vmaxList7)) {
            return null;
        }

        $average7 = array_sum($vmaxList7) / count($vmaxList7);
        $average28 = array_sum($vmaxList28) / count($vmaxList28);

        if ($average28 == 0) {
            return null;
        }

        $drop = (($average28 - $average7) / $average28) * 100;

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

        $absDrop = abs($drop);

        return match (true) {
            $absDrop >= 15 => self::ALERT_RED,
            $absDrop >= 5 => self::ALERT_ORANGE,
            default => self::ALERT_GREEN,
        };
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
                    'level' => $this->getAcwrAlertLevel($acwr),
                ];
            }
        }

        return $history;
    }

    public function getAcwrAlertLevel(?float $acwr): ?int
    {
        return match (true) {
            $acwr === null => null,
            $acwr <= 0.7 || $acwr >= 1.5 => self::ALERT_RED,
            $acwr < 0.8 || $acwr > 1.3 => self::ALERT_ORANGE,
            default => self::ALERT_GREEN,
        };
    }

    /**
     * Calcule un score de risque de 0 (aucun risque) à 100 (risque maximal).
     * Chaque métrique contribue un sous-score de 0 à 33.
     */
    public function calculRiskScore(Player $player): int
    {
        $acwr = $this->calculAcwr($player);
        $vmaxDrop = $this->calculVmaxDrop($player);
        $foster = $this->calculFosterMonotony($player);

        $subScores = [];

        // ACWR : idéal à 1.0, risque max à 0.0 ou 2.0
        if ($acwr !== null) {
            $subScores[] = min(33, 33 * abs($acwr - 1.0) / 1.0);
        }

        // Vmax Drop : idéal à 0%, risque max à 30%
        if ($vmaxDrop !== null) {
            $subScores[] = min(33, 33 * abs($vmaxDrop) / 30);
        }

        // Monotonie : idéale à 1.0, risque max à 5.0
        if ($foster !== null) {
            $subScores[] = min(33, 33 * max(0, $foster - 1.0) / 4.0);
        }

        if (empty($subScores)) {
            return 0;
        }

        $score = (array_sum($subScores) / count($subScores)) * 3;

        return (int) min(100, max(0, round($score)));
    }

    public function updatePlayerAlertLevel(Player $player): int
    {
        $riskScore = $this->calculRiskScore($player);
        $player->setScore($riskScore);

        return $riskScore;
    }

    /**
     * Retourne le niveau d'alerte (0=vert, 1=orange, 2=rouge) à partir du score de risque.
     */
    public function getRiskAlertLevel(int $riskScore): int
    {
        return match (true) {
            $riskScore > 60 => self::ALERT_RED,
            $riskScore > 30 => self::ALERT_ORANGE,
            default => self::ALERT_GREEN,
        };
    }

    /**
     * @return array{green: int, orange: int, red: int}
     */
    public function getAlertCounts(): array
    {
        $players = $this->playerRepository->findBy(['isDeleted' => false]);

        $counts = [
            'green' => 0,
            'orange' => 0,
            'red' => 0,
        ];

        foreach ($players as $player) {
            $score = $player->getScore();
            if ($score === null) {
                $counts['green']++;
                continue;
            }

            $level = $this->getRiskAlertLevel($score);
            match ($level) {
                self::ALERT_RED => $counts['red']++,
                self::ALERT_ORANGE => $counts['orange']++,
                default => $counts['green']++,
            };
        }

        return $counts;
    }
}
