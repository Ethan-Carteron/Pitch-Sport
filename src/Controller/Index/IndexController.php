<?php

namespace App\Controller\Index;

use App\Service\CalculService;
use App\Service\PlayerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class IndexController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(
        PlayerService          $playerService,
        CalculService          $calculService,
        EntityManagerInterface $entityManager
    ): Response
    {
        $activePlayers = $playerService->findActivePlayers();

        foreach ($activePlayers as $player) {
            $calculService->updatePlayerAlertLevel($player);
        }
        $entityManager->flush();

        $alertCounts = $calculService->getAlertCounts();

        return $this->render('index/index.html.twig', [
            'activePlayersCount' => count($activePlayers),
            'activePlayers' => $activePlayers,
            'greenCount' => $alertCounts['green'],
            'orangeCount' => $alertCounts['orange'],
            'redCount' => $alertCounts['red'],
        ]);
    }
}
