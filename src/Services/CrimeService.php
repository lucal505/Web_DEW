<?php

namespace App\Services;

use App\Repositories\CrimeRepository;
use InvalidArgumentException;

class CrimeService extends BaseService
{
    private CrimeRepository $repository;

    public function __construct(CrimeRepository $repository)
    {
        parent::__construct(2020);
        $this->repository = $repository;
    }

    public function getDemographics(array $filters): array
    {
        $this->validateBaseFilters($filters);
        return $this->repository->getDemographics($filters);
    }

    public function getSentences(array $filters): array
    {
        $this->validateBaseFilters($filters);
        return $this->repository->getSentences($filters);
    }

    public function getArticles(array $filters): array
    {
        $this->validateBaseFilters($filters);
        return $this->repository->getArticles($filters);
    }

    // optiunile pentru filtrare
    public function getDemographicOptions(): array
    {
        return $this->repository->getDemographicOptions();
    }

    public function getSentenceOptions(): array
    {
        return $this->repository->getSentenceOptions();
    }

    public function getArticleOptions(): array
    {
        return $this->repository->getArticleOptions();
    }
    
    // may add more specific validation methods if needed
}
