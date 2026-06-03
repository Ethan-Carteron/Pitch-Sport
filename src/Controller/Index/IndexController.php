<?php

namespace App\Controller\Index;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Service\PlayerService;

final class IndexController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(PlayerService $playerService): Response
    {
        $activePlayers = $playerService->findActivePlayers();

        return $this->render('index/index.html.twig', [
            'activePlayersCount' => count($activePlayers),
            'activePlayers' => $activePlayers,
        ]);
    }
}
