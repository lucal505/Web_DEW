<?php

namespace App\Services;

use App\Repositories\CrimeRepository;
use App\Repositories\EmergencyRepository;
use App\Repositories\PreventionRepository;
use App\Repositories\SeizureRepository;
use PDO;

class WipeService
{
    public function __construct(
        private PDO $pdo,
        private CrimeRepository $crimeRepository,
        private EmergencyRepository $emergencyRepository,
        private PreventionRepository $preventionRepository,
        private SeizureRepository $seizureRepository
    ) {}

    public function wipeAllData(): int
    {
        $this->pdo->beginTransaction();
        try {
            $count = 0;
            $count += $this->crimeRepository->wipeData();
            $count += $this->emergencyRepository->wipeData();
            $count += $this->preventionRepository->wipeData();
            $count += $this->seizureRepository->wipeData();
            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        return $count;
    }
}
