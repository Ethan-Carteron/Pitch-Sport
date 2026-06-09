<?php

namespace App\Controller\Stat;

use App\Entity\Player;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StatController extends AbstractController
{
    #[Route('/stat/{uid}', name: 'app_stat')]
    public function index
    (
        uuid $uid,
        Player $player
    ): Response
    {
        return $this->render('stat/index.html.twig', [
            'player' => $player,
        ]);
    }
}
