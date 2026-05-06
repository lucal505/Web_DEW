<?php

namespace App\Services;

use InvalidArgumentException;

abstract class BaseService
{
    protected int $minYear;

    public function __construct(int $minYear)
    {
        $this->minYear = $minYear;
    }

    protected function validateBaseFilters(array $filters): void
    {
        $currentYear = (int)date('Y');

        // an exact
        if (isset($filters['year'])) {
            $year = (int)$filters['year'];
            if ($year < $this->minYear || $year > $currentYear) {
                throw new InvalidArgumentException("Year must be between {$this->minYear} and {$currentYear}.");
            }
        }

        if (isset($filters['year']) && (isset($filters['min_year']) || isset($filters['max_year']))) {
            throw new InvalidArgumentException("Use either 'year' or 'min_year'/'max_year', not both.");
        }

        // interval de ani
        if (isset($filters['min_year'])) {
            $minYear = (int)$filters['min_year'];
            if ($minYear < $this->minYear || $minYear > $currentYear) {
                throw new InvalidArgumentException("min_year must be between {$this->minYear} and {$currentYear}.");
            }
        }

        if (isset($filters['max_year'])) {
            $maxYear = (int)$filters['max_year'];
            if ($maxYear < $this->minYear || $maxYear > $currentYear) {
                throw new InvalidArgumentException("max_year must be between {$this->minYear} and {$currentYear}.");
            }
        }

        if (isset($filters['min_year']) && isset($filters['max_year'])) {
            if ((int)$filters['min_year'] > (int)$filters['max_year']) {
                throw new InvalidArgumentException("min_year cannot be greater than max_year.");
            }
        }

        if (isset($filters['count']) && (isset($filters['min_count']) || isset($filters['max_count']))) {
            throw new InvalidArgumentException("Use either 'count' or 'min_count'/'max_count', not both.");
        }

        // interval count
        if (isset($filters['min_count']) && isset($filters['max_count'])) {
            if ((int)$filters['min_count'] > (int)$filters['max_count']) {
                throw new InvalidArgumentException("min_count cannot be greater than max_count.");
            }
        }
    }
}