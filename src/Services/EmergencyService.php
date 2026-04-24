<?php

namespace App\Services;

use App\Repositories\EmergencyRepository;
use InvalidArgumentException;

class EmergencyService
{
    private EmergencyRepository $repository;

    public function __construct(EmergencyRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getEmergencies(array $filters): array
    {
        $this->validateFilters($filters);
        return $this->repository->getEmergencies($filters);
    }

    // basic filters, extend later
    private function validateFilters(array $filters): void
    {
        if (isset($filters['year'])) {
            $year = (int)$filters['year'];
            $currentYear = (int)date('Y');
            if ($year < 2021 || $year > $currentYear) {
                throw new InvalidArgumentException("Invalid year (not a number or is in future).");
            }
        }

        if (isset($filters['min_count']) && isset($filters['max_count'])) {
            if ((int)$filters['min_count'] > (int)$filters['max_count']) {
                throw new InvalidArgumentException("Min_count greater than max_count.");
            }
        }
    }
}
