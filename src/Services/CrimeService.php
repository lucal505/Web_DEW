<?php

namespace App\Services;

use App\Repositories\CrimeRepository;
use App\DTOs\Crime\CrimeArticleFilterDTO;
use App\DTOs\Crime\CrimeGeneralFilterDTO;
use App\DTOs\Crime\CrimeDemographicFilterDTO;
use App\DTOs\Crime\CrimeSentenceFilterDTO;

class CrimeService extends BaseService
{
    private CrimeRepository $repository;

    public function __construct(CrimeRepository $repository)
    {
        parent::__construct(2020);
        $this->repository = $repository;
    }

    public function getDemographics(CrimeDemographicFilterDTO $filterDTO): array
    {
        $this->validateBaseFilters($filterDTO);

        if ($filterDTO->getPage() !== null) {
            $result = $this->repository->getDemographicsPaginated($filterDTO, static::PER_PAGE);

            return [
                'data' => $result['data'],
                'pagination' => [
                    'page' => $filterDTO->getPage(),
                    'per_page' => static::PER_PAGE,
                    'total' => $result['total'],
                    'total_pages' => ceil($result['total'] / static::PER_PAGE),
                ]
            ];
        }

        return $this->repository->getDemographics($filterDTO);
    }

    public function getSentences(CrimeSentenceFilterDTO $filterDTO): array
    {
        $this->validateBaseFilters($filterDTO);

        if ($filterDTO->getPage() !== null) {
            $result = $this->repository->getSentencesPaginated($filterDTO, static::PER_PAGE);

            return [
                'data' => $result['data'],
                'pagination' => [
                    'page' => $filterDTO->getPage(),
                    'per_page' => static::PER_PAGE,
                    'total' => $result['total'],
                    'total_pages' => ceil($result['total'] / static::PER_PAGE),
                ]
            ];
        }

        return $this->repository->getSentences($filterDTO);
    }

    public function getArticles(CrimeArticleFilterDTO $filterDTO): array
    {
        $this->validateBaseFilters($filterDTO);

        if ($filterDTO->getPage() !== null) {
            $result = $this->repository->getArticlesPaginated($filterDTO, static::PER_PAGE);

            return [
                'data' => $result['data'],
                'pagination' => [
                    'page' => $filterDTO->getPage(),
                    'per_page' => static::PER_PAGE,
                    'total' => $result['total'],
                    'total_pages' => ceil($result['total'] / static::PER_PAGE),
                ]
            ];
        }

        return $this->repository->getArticles($filterDTO);
    }

    public function getGeneral(CrimeGeneralFilterDTO $filterDTO): array
    {
        $this->validateBaseFilters($filterDTO);
        if ($filterDTO->getPage() !== null) {
            $result = $this->repository->getGeneralPaginated($filterDTO, static::PER_PAGE);
            return [
                'data'       => $result['data'],
                'pagination' => [
                    'page'        => $filterDTO->getPage(),
                    'per_page'    => static::PER_PAGE,
                    'total'       => $result['total'],
                    'total_pages' => (int)ceil($result['total'] / static::PER_PAGE),
                ]
            ];
        }

        return $this->repository->getGeneral($filterDTO);
    }

    public function getGroups(CrimeGeneralFilterDTO $filterDTO): array
    {
        $this->validateBaseFilters($filterDTO);

        if ($filterDTO->getPage() !== null) {
            $result = $this->repository->getGroupsPaginated($filterDTO, static::PER_PAGE);
            return [
                'data'       => $result['data'],
                'pagination' => [
                    'page'        => $filterDTO->getPage(),
                    'per_page'    => static::PER_PAGE,
                    'total'       => $result['total'],
                    'total_pages' => (int)ceil($result['total'] / static::PER_PAGE),
                ]
            ];
        }

        return $this->repository->getGroups($filterDTO);
    }

    public function getGeneralOptions(): array
    {
        return $this->repository->getGeneralOptions();
    }

    public function getGroupOptions(): array
    {
        return $this->repository->getGroupOptions();
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
