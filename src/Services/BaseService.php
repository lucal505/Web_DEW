<?php

namespace App\Services;

use App\DTOs\BaseFilterDTO;
use InvalidArgumentException;

abstract class BaseService
{
    protected const PER_PAGE = 10;
    protected int $minYear;

    public function __construct(int $minYear)
    {
        $this->minYear = $minYear;
    }

    protected function validateBaseFilters(BaseFilterDTO $filterDTO): void
    {
        $currentYear = (int)date('Y');

        // an exact
        if ($filterDTO->getYear() !== null) {
            $year = $filterDTO->getYear();
            if ($year < $this->minYear || $year > $currentYear) {
                throw new InvalidArgumentException("Year must be between {$this->minYear} and {$currentYear}.");
            }
        }

        if ($filterDTO->getYear() !== null && ($filterDTO->getMinYear() !== null || $filterDTO->getMaxYear() !== null)) {
            throw new InvalidArgumentException("Use either 'year' or 'min_year'/'max_year', not both.");
        }

        // interval de ani
        if ($filterDTO->getMinYear() !== null) {
            $minYear = $filterDTO->getMinYear();
            if ($minYear < $this->minYear || $minYear > $currentYear) {
                throw new InvalidArgumentException("'min_year' must be between {$this->minYear} and {$currentYear}.");
            }
        }

        if ($filterDTO->getMaxYear() !== null) {
            $maxYear = $filterDTO->getMaxYear();
            if ($maxYear < $this->minYear || $maxYear > $currentYear) {
                throw new InvalidArgumentException("'max_year' must be between {$this->minYear} and {$currentYear}.");
            }
        }

        if ($filterDTO->getMinYear() !== null && $filterDTO->getMaxYear() !== null) {
            if ($filterDTO->getMinYear() > $filterDTO->getMaxYear()) {
                throw new InvalidArgumentException("'min_year' cannot be greater than max_year.");
            }
        }

        if ($filterDTO->getCount() !== null && ($filterDTO->getMinCount() !== null || $filterDTO->getMaxCount() !== null)) {
            throw new InvalidArgumentException("Use either 'count' or 'min_count'/'max_count', not both.");
        }

        // interval count
        if ($filterDTO->getMinCount() !== null && $filterDTO->getMaxCount() !== null) {
            if ($filterDTO->getMinCount() > $filterDTO->getMaxCount()) {
                throw new InvalidArgumentException("'min_count' cannot be greater than max_count.");
            }
        }

        if ($filterDTO->getPage() !== null && $filterDTO->getPage() < 1) {
            throw new InvalidArgumentException("'page' must be a positive integer.");
        }
    }

    // helperi de validare pentru CRUD
    protected function validateId(int $id): void
    {
        if ($id < 1) {
            throw new InvalidArgumentException('Id must be a positive integer.');
        }
    }

    protected function validateYear(?int $year): void
    {
        if ($year === null) {
            throw new InvalidArgumentException('Year is required.');
        }

        $currentYear = (int)date('Y');
        if ($year < $this->minYear || $year > $currentYear) {
            throw new InvalidArgumentException("Year must be between {$this->minYear} and {$currentYear}.");
        }
    }

    // verifica un an optional (la update)
    protected function validateOptionalYear(?int $year): void
    {
        if ($year !== null) {
            $this->validateYear($year);
        }
    }

    // verifica un numar optional sa nu fie negativ
    protected function validateNonNegative(?int $value, string $fieldName): void
    {
        if ($value !== null && $value < 0) {
            throw new InvalidArgumentException("$fieldName must be a non-negative integer.");
        }
    }
}