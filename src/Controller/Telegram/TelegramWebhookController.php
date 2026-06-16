<?php

namespace App\Controller\Telegram;

use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;

final class TelegramWebhookController extends AbstractController
{
    #[Route('/webhook/telegram', name: 'app_webhook_telegram', methods: ['POST'])]
    public function index(
        Request $request,
        PlayerRepository $playerRepository,
        EntityManagerInterface $entityManager,
        ChatterInterface $chatter
    ): Response {
        $content = json_decode($request->getContent(), true);

        if (!isset($content['message']['text']) || !isset($content['message']['chat']['id'])) {
            return new Response('OK');
        }

        $text = $content['message']['text'];
        $chatId = (string) $content['message']['chat']['id'];

        if (str_starts_with($text, '/start')) {
            $parts = explode(' ', $text);
            if (count($parts) === 2) {
                $uid = trim($parts[1]);
                $player = $playerRepository->findOneBy(['uid' => $uid]);

                if ($player) {
                    $player->setTelegramChatId($chatId);
                    $entityManager->flush();

                    $message = new ChatMessage("Bonjour {$player->getName()} ! Ton compte Telegram est maintenant lié à PitchSport. Tu recevras tes questionnaires ici.");
                    $message->transport('telegram');
                    $message->options((new TelegramOptions())->chatId($chatId));
                    $chatter->send($message);
                } else {
                    $message = new ChatMessage("Le code joueur est invalide ou introuvable.");
                    $message->transport('telegram');
                    $message->options((new TelegramOptions())->chatId($chatId));
                    $chatter->send($message);
                }
            } else {
                $message = new ChatMessage("Bienvenue sur PitchSport ! Utilise le lien d'invitation fourni par ton coach pour lier ton compte.");
                $message->transport('telegram');
                $message->options((new TelegramOptions())->chatId($chatId));
                $chatter->send($message);
            }
        }

        return new Response('OK');
    }
}

