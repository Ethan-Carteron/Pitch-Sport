<?php

namespace App\Controller\Import;

use App\Entity\Club;
use App\Entity\Player;
use App\Entity\User;
use App\Entity\Workload;
use App\Service\ImportManagerService;
use App\Service\PlayerService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Iterator;
use League\Csv\Exception;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class ImportController extends AbstractController
{
    /**
     * @throws Exception
     * @throws ORMException
     */
    #[Route('/import', name: 'app_import', methods: ['GET', 'POST'])]
    public function import(
        Request                $request,
        PlayerService          $playerService,
        EntityManagerInterface $entityManagerInterface,
        #[CurrentUser] User    $user

    ): Response
    {
        if ($request->isMethod('GET')) {
            return $this->render('import/import.html.twig');
        }

        $club = $user->getClub();

        $csvFile = $request->files->get('csv');

        if (!$csvFile) {
            $this->addFlash('warning', 'CSV file is empty.');
            return $this->redirectToRoute('app_import');
        }

        $reader = Reader::from($csvFile->getPathname());
        $reader->setHeaderOffset(0);

        $stats = ['success' => 0, 'skipped' => 0];

        $records = $reader->getRecords();

        $stats = $this->csvReading(
            $records,
            $playerService,
            $club,
            $user,
            $entityManagerInterface,
            $stats
        );
        $entityManagerInterface->flush();
        $this->successRateManager($stats['success'], $stats['skipped']);

        return $this->redirectToRoute('app_import');
    }

    public function formatData(array $record): array
    {
        $data = [];
        foreach ($record as $key => $value) {
            $data[strtolower(trim((string)$key))] = $value;
        }
        return $data;
    }

    public function workloadInitialization(
        Player                 $player,
        User                   $user,
        array                  $workloadData,
        EntityManagerInterface $entityManagerInterface
    ): void
    {
        $workload = new Workload();
        $workload->setPlayer($player);
        $workload->setMaxSpeed($workloadData['maxSpeed']);
        $workload->setTotalDistance($workloadData['totalDistance']);
        $workload->setCreatedBy($user);
        $workload->setCreatedDate($workloadData['date']);

        $entityManagerInterface->persist($workload);
    }

    public function successRateManager(int $countSuccess, int $countSkipped): void
    {
        if ($countSuccess > 0) {
            $this->addFlash('success', sprintf('%d lignes importées avec succès.', $countSuccess));
        }
        if ($countSkipped > 0) {
            $this->addFlash('warning', sprintf('%d lignes ignorées (format invalide ou colonnes manquantes).', $countSkipped));
        }
        if ($countSuccess === 0 && $countSkipped === 0) {
            $this->addFlash('info', 'Le fichier CSV semble vide.');
        }
    }

    public function csvReading(
        Iterator               $records,
        PlayerService          $playerService,
        Club                   $club,
        User                   $user,
        EntityManagerInterface $em,
        array                  $stats
    ): array
    {
        $playerCache = [];

        foreach ($records as $record) {
            $data = $this->formatData($record);

            $dateValue = $data['date'] ?? null;
            if (!$dateValue) {
                $stats['skipped']++;
                continue;
            }

            $date = DateTime::createFromFormat('d/m/Y', $dateValue) ?: DateTime::createFromFormat('Y-m-d', $dateValue);
            if (!$date) {
                $stats['skipped']++;
                continue;
            }

            $name = $data['name'] ?? '';
            if (empty($name)) {
                $stats['skipped']++;
                continue;
            }

            $cacheKey = mb_strtolower($name);

            if (!isset($playerCache[$cacheKey])) {
                $player = $playerService->findPlayerByNameInClub($name, $club);
                if (!$player) {
                    $player = $playerService->createPlayer($name, $club, $user);
                }
                $playerCache[$cacheKey] = $player;
            }

            $player = $playerCache[$cacheKey];

            $workloadData = [
                'maxSpeed' => (float)str_replace(',', '.', $data['maximum velocity (km/h)'] ?? $data['max_speed'] ?? '0'),
                'totalDistance' => (float)str_replace(',', '.', $data['total distance (m)'] ?? $data['total_distance'] ?? '0'),
                'date' => $date
            ];

            $this->workloadInitialization($player, $user, $workloadData, $em);
            $stats['success']++;
        }
        return $stats;
    }
}
