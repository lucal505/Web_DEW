<?php

namespace App\Services;

use App\Repositories\SeizureRepository;

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

        if ($this->isPaginated($filters)) {
            $page = $this->getPage($filters);
            $result = $this->repository->getSeizuresPaginated($filters, $page, static::PER_PAGE);

            return [
                'data' => $result['data'],
                'pagination' => [
                    'page' => $page,
                    'per_page' => static::PER_PAGE,
                    'total' => $result['total'],
                    'total_pages' => ceil($result['total'] / static::PER_PAGE),
                ]
            ];
        }

        return $this->repository->getSeizures($filters);
    }

    public function getOptions(): array
    {
        return $this->repository->getOptions();
    }

    // may add more specific validation methods if needed
}
