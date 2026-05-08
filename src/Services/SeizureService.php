<?php

namespace App\Services;

use App\Repositories\SeizureRepository;
use InvalidArgumentException;

class SeizureService extends BaseService
{
    private SeizureRepository $repository;

    public function __construct(SeizureRepository $repository)
    {
        parent::__construct(2020);
        $this->repository = $repository;
    }

    public function getSeizures(array $filters): array
    {
        $this->validateBaseFilters($filters);
        return $this->repository->getSeizures($filters);
    }

    public function getOptions(): array
    {
        return $this->repository->getOptions();
    }

    // may add more specific validation methods if needed
}
