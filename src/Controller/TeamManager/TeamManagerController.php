<?php

namespace App\Controller\TeamManager;

use App\Service\PlayerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class TeamManagerController extends AbstractController
{
    #[Route('/team-manager', name: 'app_team_manager', methods: ['GET'])]
    public function index(PlayerService $playerService): Response
    {
        $activePlayers = $playerService->findActivePlayers();

        return $this->render('team_manager/index.html.twig', [
            'activePlayers' => $activePlayers,
        ]);
    }

    #[Route('/team-manager/player/{uid}/phone', name: 'app_team_manager_phone', methods: ['POST'])]
    public function updatePhone(
        string $uid,
        Request $request,
        PlayerService $playerService,
        EntityManagerInterface $entityManager
    ): Response {
        $activePlayers = $playerService->findActivePlayers();
        $player = null;
        foreach ($activePlayers as $p) {
            if ($p->getUid() === $uid) {
                $player = $p;
                break;
            }
        }

        if (!$player) {
            throw $this->createNotFoundException('Joueur introuvable.');
        }

        $phone = $request->request->get('phone');
        $player->setPhoneNumber($phone);
        
        $entityManager->flush();

        $this->addFlash('success', 'Numéro de téléphone mis à jour pour ' . $player->getName());

        return $this->redirectToRoute('app_team_manager');
    }

    #[Route('/team-manager/send-telegram', name: 'app_team_manager_telegram_send', methods: ['POST'])]
    public function sendTelegram(
        PlayerService $playerService,
        \Symfony\Component\Notifier\ChatterInterface $chatter,
        UrlGeneratorInterface $urlGenerator
    ): Response {
        $activePlayers = $playerService->findActivePlayers();
        $count = 0;

        foreach ($activePlayers as $player) {
            if ($player->getTelegramChatId()) {
                $questionnaireUrl = $urlGenerator->generate('app_questionnaire_wellness', ['uid' => $player->getUid()], UrlGeneratorInterface::ABSOLUTE_URL);
                
                $message = new \Symfony\Component\Notifier\Message\ChatMessage(
                    "Bonjour {$player->getName()}, voici ton questionnaire de forme du jour : {$questionnaireUrl}"
                );
                $message->transport('telegram');
                $message->options((new \Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions())->chatId($player->getTelegramChatId()));
                
                try {
                    $chatter->send($message);
                    $count++;
                } catch (\Exception $e) {
                    // Ignore errors for individual players to not break the loop
                }
            }
        }

        $this->addFlash('success', "Questionnaires envoyés à {$count} joueuse(s) via Telegram.");

        return $this->redirectToRoute('app_team_manager');
    }
}
