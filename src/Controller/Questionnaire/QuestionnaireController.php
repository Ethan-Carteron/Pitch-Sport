<?php

namespace App\Controller\Questionnaire;

use App\Entity\WellnessQuestions;
use App\Service\PlayerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use DateTime;

final class QuestionnaireController extends AbstractController
{
    #[Route('/questionnaire/wellness/{uid}', name: 'app_questionnaire_wellness', methods: ['GET', 'POST'])]
    public function index(
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

        $submitted = false;

        if ($request->isMethod('POST')) {
            $fatigue = $request->request->get('fatigue');
            $sleep = $request->request->get('sleep');
            $stress = $request->request->get('stress');

            if ($fatigue !== null && $sleep !== null && $stress !== null) {
                $wellness = new WellnessQuestions();
                $wellness->setPlayer($player);
                $wellness->setFatigue((int) $fatigue);
                $wellness->setSleep((int) $sleep);
                $wellness->setStress((int) $stress);
                $wellness->setCreatedDate(new DateTime());
                
                $entityManager->persist($wellness);
                $entityManager->flush();

                $submitted = true;
            }
        }

        return $this->render('questionnaire/index.html.twig', [
            'player' => $player,
            'submitted' => $submitted,
        ]);
    }
}
