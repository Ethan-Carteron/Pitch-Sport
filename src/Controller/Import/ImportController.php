<?php

namespace App\Controller\Import;

use App\Entity\Workload;
use App\Service\ImportManagerService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use League\Csv\Exception;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class ImportController extends AbstractController
{
    /**
     * @throws Exception
     * @throws ORMException
     */
    #[Route('/import', name: 'app_import', methods: ['GET','POST'])]
    public function import(
        Request $request,
        ImportManagerService $importManager,
        EntityManagerInterface $entityManagerInterface,
        #[CurrentUser] User $user

    ): Response
    {
        if ($request->isMethod('GET')) {
            return $this->render('import/import.html.twig');
        }

        $club = $user->getClub();

        if (!$club) {
            $this->addFlash('danger', 'Votre compte n\'est rattaché à aucun club. Veuillez contacter un administrateur.');
            return $this->redirectToRoute('app_import');
        }

        $csvFile = $request->files->get('csv');

        if (!$csvFile) {
            $this->addFlash('warning', 'CSV file is empty.');
            return $this->redirectToRoute('app_import');
        }

        $reader = Reader::from($csvFile->getPathname());
        $reader->setHeaderOffset(0);

        // Détection du délimiteur (souvent ; dans les exports Excel français)
        $content = file_get_contents($csvFile->getPathname());
        if (str_contains($content, ';') && !str_contains($content, ',')) {
            $reader->setDelimiter(';');
        }

        $records = $reader->getRecords();
        $countSuccess = 0;
        $countSkipped = 0;

        foreach ($records as $record) {
            // Normalisation des clés : minuscules et suppression des espaces
            $data = [];
            foreach ($record as $key => $value) {
                $data[strtolower(trim((string)$key))] = $value;
            }

            // Vérification de la présence de la colonne date (supporte 'date' ou 'Date')
            $dateValue = $data['date'] ?? null;
            if (!$dateValue) {
                $countSkipped++;
                continue;
            }

            // Supporte d/m/Y et Y-m-d
            $date = DateTime::createFromFormat('d/m/Y', $dateValue) ?: DateTime::createFromFormat('Y-m-d', $dateValue);
            if (!$date) {
                $countSkipped++;
                continue;
            }

            // Récupération du nom (colonne 'name' ou 'Name')
            $name = $data['name'] ?? '';
            if (empty($name)) {
                $countSkipped++;
                continue;
            }

            $player = $importManager->findPlayerByNameInClub(
                $name,
                $club
            );

            if (!$player) {
                $player = $importManager->createPlayer(
                    $name,
                    $club,
                    $user
                );
            }

            // Conversion des virgules en points pour les nombres
            $maxSpeedStr = str_replace(',', '.', $data['maximum velocity (km/h)'] ?? $data['max_speed'] ?? '0');
            $totalDistanceStr = str_replace(',', '.', $data['total distance (m)'] ?? $data['total_distance'] ?? '0');

            $workload = new Workload();
            $workload->setPlayer($player);
            $workload->setMaxSpeed((float)$maxSpeedStr);
            $workload->setTotalDistance((float)$totalDistanceStr);
            $workload->setCreatedBy($user);
            $workload->setCreatedDate($date);

            $entityManagerInterface->persist($workload);
            $countSuccess++;
        }
        $entityManagerInterface->flush();

        if ($countSuccess > 0) {
            $this->addFlash('success', sprintf('%d lignes importées avec succès.', $countSuccess));
        }
        if ($countSkipped > 0) {
            $this->addFlash('warning', sprintf('%d lignes ignorées (format invalide ou colonnes manquantes).', $countSkipped));
        }
        if ($countSuccess === 0 && $countSkipped === 0) {
            $this->addFlash('info', 'Le fichier CSV semble vide.');
        }

        return $this->redirectToRoute('app_import');
    }
}
