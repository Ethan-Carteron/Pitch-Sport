<?php

namespace App\Controller\Stat;

use App\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StatController extends AbstractController
{
    #[Route('/stat/{uid}', name: 'app_stat')]
    public function index(
        string $uid,
        PlayerRepository $playerRepository,
        \App\Service\CalculService $calculService,
        \App\Service\AlertAdviceService $alertAdviceService
    ): Response
    {
        $player = $playerRepository->findOneBy(['uid' => $uid]);

        if (!$player) {
            throw $this->createNotFoundException('Joueur introuvable.');
        }

        $acwrHistory = $calculService->getAcwrHistory($player);
        $vmaxDropHistory = $calculService->getVmaxDropHistory($player);
        $distanceHistory = $calculService->getDistanceHistory($player);
        $fosterHistory = $calculService->getFosterHistory($player);

        $currentAcwr = $calculService->calculAcwr($player);
        $currentAcwrLevel = $calculService->getAcwrAlertLevel($currentAcwr);

        $currentVmaxDrop = $calculService->calculVmaxDrop($player);
        $currentVmaxLevel = $calculService->getVmaxDropAlertLevel($currentVmaxDrop);

        $currentFoster = $calculService->calculFosterMonotony($player);
        $currentFosterLevel = $calculService->getFosterAlertLevel($currentFoster);

        $riskScore = $calculService->calculRiskScore($player);
        $riskLevel = $calculService->getRiskAlertLevel($riskScore);
        $advices = $alertAdviceService->getAdvices($currentAcwr, $currentVmaxDrop, $currentFoster);

        return $this->render('stat/index.html.twig', [
            'player' => $player,
            'acwrHistory' => $acwrHistory,
            'vmaxDropHistory' => $vmaxDropHistory,
            'distanceHistory' => $distanceHistory,
            'fosterHistory' => $fosterHistory,
            'currentAcwr' => $currentAcwr,
            'currentAcwrLevel' => $currentAcwrLevel,
            'currentVmaxDrop' => $currentVmaxDrop,
            'currentVmaxLevel' => $currentVmaxLevel,
            'currentFoster' => $currentFoster,
            'currentFosterLevel' => $currentFosterLevel,
            'riskScore' => $riskScore,
            'riskLevel' => $riskLevel,
            'advices' => $advices,
        ]);
    }
}
