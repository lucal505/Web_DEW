<?php

namespace App\Services;

use App\Repositories\PreventionRepository;
use InvalidArgumentException;

class PreventionService
{
    private PreventionRepository $repository;

    public function __construct(PreventionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getProjects(array $filters): array
    {
        $this->validateFilters($filters);
        return $this->repository->getProjects($filters);
    }

    public function getCampaigns(array $filters): array
    {
        $this->validateFilters($filters);
        return $this->repository->getCampaigns($filters);
    }

    public function getActivities(array $filters): array
    {
        $this->validateFilters($filters);
        return $this->repository->getActivities($filters);
    }

    // basic filters, extend later
    private function validateFilters(array $filters): void
    {
        if (isset($filters['year'])) {
            $year = (int)$filters['year'];
            $currentYear = (int)date('Y');
            if ($year < 2018 || $year > $currentYear) {
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
