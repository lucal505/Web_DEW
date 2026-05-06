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

    // may add more specific validation methods if needed
}
