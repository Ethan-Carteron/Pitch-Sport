<?php

namespace App\Controller\Calcul;

use App\Service\CalculService;
use App\Service\PlayerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CalculController extends AbstractController
{
    #[Route('/calcul', name: 'app_calcul')]
    public function index(
        PlayerService          $playerService,
        CalculService          $calculService,
        EntityManagerInterface $entityManager
    ): Response
    {
        $activePlayers = $playerService->findActivePlayers();

        $alertCounts = [
            CalculService::ALERT_GREEN => 0,
            CalculService::ALERT_ORANGE => 0,
            CalculService::ALERT_RED => 0,
        ];

        foreach ($activePlayers as $player) {
            $riskScore = $calculService->updatePlayerAlertLevel($player);
            $level = $calculService->getRiskAlertLevel($riskScore);
            $alertCounts[$level]++;
        }
        $entityManager->flush();

        return $this->render('index/index.html.twig', [
            'goodLoadPlayers' => $alertCounts[CalculService::ALERT_GREEN],
            'riskLoadPlayers' => $alertCounts[CalculService::ALERT_ORANGE],
            'overloadedPlayers' => $alertCounts[CalculService::ALERT_RED],
        ]);
    }
}
