<?php

namespace App\Service;


use App\Entity\Player;
use App\Entity\User;
use App\Entity\Workload;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\UnavailableStream;

readonly class CsvImportService
{
    public function __construct
    (
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * @throws UnavailableStream
     * @throws Exception
     */
    public function importWorkload
    (
        string $filePath,
        Player $player,
        User $user
    ): int
    {
        $csv = Reader::from($filePath);
        $csv->setHeaderOffset(0);

        $records = $csv->getRecords();
        $count = 0;

        foreach ($records as $record) {
            $workload = new Workload();
            $workload->setPlayer($player);
            $workload->setTotalDistance((float) $record['distance']);
            $workload->setMaxSpeed((float) $record['speed']);
            $workload->setDuration((float) $record['duration']);
            $workload->setAcceleration((float) $record['acceleration']);
            $workload->setCreatedBy($user);
            $workload->setCreatedDate(new DateTime());

            $this->entityManager->persist($workload);

            if (($count % 100) === 0) {
                $this->entityManager->flush();
            }
        }
        $this->entityManager->flush();
        return $count;
    }
}
