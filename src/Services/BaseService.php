<?php

namespace App\Services;

use App\DTOs\BaseFilterDTO;
use InvalidArgumentException;

abstract class BaseService
{
    protected const PER_PAGE = 5;
    protected int $minYear;

    public function __construct(int $minYear)
    {
        $this->minYear = $minYear;
    }

    protected function validateBaseFilters(BaseFilterDTO $filterDTO): void
    {
        $currentYear = (int)date('Y');

        // an exact
        if (isset($filterDTO->getYear())) {
            $year = $filterDTO->getYear();
            if ($year < $this->minYear || $year > $currentYear) {
                throw new InvalidArgumentException("Year must be between {$this->minYear} and {$currentYear}.");
            }
        }

        if (isset($filterDTO->getYear()) && (isset($filterDTO->getMinYear()) || isset($filterDTO->getMaxYear()))) {
            throw new InvalidArgumentException("Use either 'year' or 'min_year'/'max_year', not both.");
        }

        // interval de ani
        if (isset($filterDTO->getMinYear())) {
            $minYear = $filterDTO->getMinYear();
            if ($minYear < $this->minYear || $minYear > $currentYear) {
                throw new InvalidArgumentException("'min_year' must be between {$this->minYear} and {$currentYear}.");
            }
        }

        if (isset($filterDTO->getMaxYear())) {
            $maxYear = $filterDTO->getMaxYear();
            if ($maxYear < $this->minYear || $maxYear > $currentYear) {
                throw new InvalidArgumentException("'max_year' must be between {$this->minYear} and {$currentYear}.");
            }
        }

        if (isset($filterDTO->getMinYear()) && isset($filterDTO->getMaxYear())) {
            if ($filterDTO->getMinYear() > $filterDTO->getMaxYear()) {
                throw new InvalidArgumentException("'min_year' cannot be greater than max_year.");
            }
        }

        if (isset($filterDTO->getCount()) && (isset($filterDTO->getMinCount()) || isset($filterDTO->getMaxCount()))) {
            throw new InvalidArgumentException("Use either 'count' or 'min_count'/'max_count', not both.");
        }

        // interval count
        if (isset($filterDTO->getMinCount()) && isset($filterDTO->getMaxCount())) {
            if ($filterDTO->getMinCount() > $filterDTO->getMaxCount()) {
                throw new InvalidArgumentException("'min_count' cannot be greater than max_count.");
            }
        }

        if (isset($filterDTO->getPage()) && $filterDTO->getPage() < 1) {
            throw new InvalidArgumentException("'page' must be a positive integer.");
        }
    }
}