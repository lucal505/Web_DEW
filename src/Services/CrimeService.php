<?php

namespace App\Services;

use App\Repositories\CrimeRepository;
use InvalidArgumentException;

class CrimeService
{
    private CrimeRepository $repository;

    public function __construct(CrimeRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getDemographics(array $filters): array
    {
        $this->validateFilters($filters);
        return $this->repository->getDemographics($filters);
    }

    public function getSentences(array $filters): array
    {
        $this->validateFilters($filters);
        return $this->repository->getSentences($filters);
    }

    public function getArticles(array $filters): array
    {
        $this->validateFilters($filters);
        return $this->repository->getArticles($filters);
    }

    // basic validation, extend later
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
