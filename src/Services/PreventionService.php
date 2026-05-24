<?php

namespace App\Services;

use App\DTOs\Prevention\PreventionFilterDTO;
use App\DTOs\Prevention\PreventionProjectDTO;
use App\DTOs\Prevention\PreventionCampaignDTO;
use App\DTOs\Prevention\PreventionActivityDTO;
use App\DTOs\Prevention\PreventionProjectCreateDTO;
use App\DTOs\Prevention\PreventionProjectUpdateDTO;
use App\DTOs\Prevention\PreventionCampaignCreateDTO;
use App\DTOs\Prevention\PreventionCampaignUpdateDTO;
use App\DTOs\Prevention\PreventionActivityCreateDTO;
use App\DTOs\Prevention\PreventionActivityUpdateDTO;
use App\Repositories\PreventionRepository;
use InvalidArgumentException;

class PreventionService extends BaseService
{
    private PreventionRepository $repository;

    public function __construct(PreventionRepository $repository)
    {
        parent::__construct(2020);
        $this->repository = $repository;
    }

    public function getProjects(PreventionFilterDTO $filterDTO): array
    {
        $this->validateBaseFilters($filterDTO);

        if ($filterDTO->getPage() !== null) {
            $result = $this->repository->getProjectsPaginated($filterDTO, static::PER_PAGE);

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

        return $this->repository->getProjects($filterDTO);
    }

    public function getCampaigns(PreventionFilterDTO $filterDTO): array
    {
        $this->validateBaseFilters($filterDTO);

        if ($filterDTO->getPage() !== null) {
            $result = $this->repository->getCampaignsPaginated($filterDTO, static::PER_PAGE);

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

        return $this->repository->getCampaigns($filterDTO);
    }

    public function getActivities(PreventionFilterDTO $filterDTO): array
    {
        $this->validateBaseFilters($filterDTO);

        if ($filterDTO->getPage() !== null) {
            $result = $this->repository->getActivitiesPaginated($filterDTO, static::PER_PAGE);

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
        return $this->repository->getActivities($filterDTO);
    }

    public function getProjectOptions(): array
    {
        return $this->repository->getProjectOptions();
    }

    public function getCampaignOptions(): array
    {
        return $this->repository->getCampaignOptions();
    }

    public function getActivityOptions(): array
    {
        return $this->repository->getActivityOptions();
    }

    // CRUD: prevention_projects
    public function createProject(PreventionProjectCreateDTO $dto): PreventionProjectDTO
    {
        $this->validateYear($dto->getYear());
        if ($dto->getProjectName() === '') {
            throw new InvalidArgumentException('Project name is required.');
        }
        $this->validateNonNegative($dto->getBeneficiariesCount(), 'Beneficiaries count');
        return $this->repository->createProject($dto);
    }

    public function updateProject(int $id, PreventionProjectUpdateDTO $dto): ?PreventionProjectDTO
    {
        $this->assertId($id);
        if (
            $dto->getYear() === null && $dto->getProjectName() === null
            && $dto->getBeneficiariesCount() === null
        ) {
            throw new InvalidArgumentException('At least one field must be provided for update.');
        }
        $this->validateOptionalYear($dto->getYear());
        if ($dto->getProjectName() !== null && $dto->getProjectName() === '') {
            throw new InvalidArgumentException('Project name cannot be empty.');
        }
        $this->validateNonNegative($dto->getBeneficiariesCount(), 'Beneficiaries count');
        return $this->repository->updateProject($id, $dto);
    }

    public function deleteProject(int $id): bool
    {
        $this->assertId($id);
        return $this->repository->deleteProject($id);
    }

    // CRUD: prevention_campaigns
    public function createCampaign(PreventionCampaignCreateDTO $dto): PreventionCampaignDTO
    {
        $this->validateYear($dto->getYear());
        if ($dto->getCampaignName() === '') {
            throw new InvalidArgumentException('Campaign name is required.');
        }
        $this->validateNonNegative($dto->getBeneficiariesCount(), 'Beneficiaries count');
        return $this->repository->createCampaign($dto);
    }

    public function updateCampaign(int $id, PreventionCampaignUpdateDTO $dto): ?PreventionCampaignDTO
    {
        $this->assertId($id);
        if (
            $dto->getYear() === null && $dto->getCampaignName() === null
            && $dto->getBeneficiariesCount() === null
        ) {
            throw new InvalidArgumentException('At least one field must be provided for update.');
        }
        $this->validateOptionalYear($dto->getYear());
        if ($dto->getCampaignName() !== null && $dto->getCampaignName() === '') {
            throw new InvalidArgumentException('Campaign name cannot be empty.');
        }
        $this->validateNonNegative($dto->getBeneficiariesCount(), 'Beneficiaries count');
        return $this->repository->updateCampaign($id, $dto);
    }

    public function deleteCampaign(int $id): bool
    {
        $this->assertId($id);
        return $this->repository->deleteCampaign($id);
    }

    // CRUD: prevention_activities
    public function createActivity(PreventionActivityCreateDTO $dto): PreventionActivityDTO
    {
        $this->validateYear($dto->getYear());
        if ($dto->getSetting() === '') {
            throw new InvalidArgumentException('Setting is required.');
        }
        $this->validateNonNegative($dto->getActivitiesCount(), 'Activities count');
        $this->validateNonNegative($dto->getBeneficiariesCount(), 'Beneficiaries count');
        return $this->repository->createActivity($dto);
    }

    public function updateActivity(int $id, PreventionActivityUpdateDTO $dto): ?PreventionActivityDTO
    {
        $this->assertId($id);
        if (
            $dto->getYear() === null && $dto->getSetting() === null
            && $dto->getActivitiesCount() === null && $dto->getBeneficiariesCount() === null
            && $dto->getBeneficiaryType() === null
        ) {
            throw new InvalidArgumentException('At least one field must be provided for update.');
        }
        $this->validateOptionalYear($dto->getYear());
        if ($dto->getSetting() !== null && $dto->getSetting() === '') {
            throw new InvalidArgumentException('Setting cannot be empty.');
        }
        $this->validateNonNegative($dto->getActivitiesCount(), 'Activities count');
        $this->validateNonNegative($dto->getBeneficiariesCount(), 'Beneficiaries count');
        return $this->repository->updateActivity($id, $dto);
    }

    public function deleteActivity(int $id): bool
    {
        $this->assertId($id);
        return $this->repository->deleteActivity($id);
    }

    private function assertId(int $id): void
    {
        if ($id < 1) {
            throw new InvalidArgumentException('Id must be a positive integer.');
        }
    }
}
