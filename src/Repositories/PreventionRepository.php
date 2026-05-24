<?php

namespace App\Repositories;

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
use RuntimeException;

class PreventionRepository extends BaseRepository
{
    public function wipeData(): int
    {
        $tables = [
            'prevention_projects',
            'prevention_campaigns',
            'prevention_activities',
        ];

        foreach ($tables as $table) {
            $this->pdo->exec("DELETE FROM $table");
        }

        $tableList = "'" . implode("','", $tables) . "'";
        $this->pdo->exec("DELETE FROM sqlite_sequence WHERE name IN ($tableList)");

        return count($tables);
    }
    private function buildProjectsQuerry(PreventionFilterDTO $filterDTO): array
    {
        $data = $filterDTO->toArray();
        $sql = "SELECT * FROM prevention_projects WHERE 1=1";
        $params = [];

        if (!empty($data['name'])) {
            $sql .= " AND project_name LIKE :name";
            $params['name'] = "%" . $data['name'] . "%";
        }

        // metoda din parinte pentru aplicare filtre
        $this->applyCommonFilters($sql, $params, $data, 'year', 'beneficiaries_count');

        return [$sql, $params];
    }

    private function buildCampaignsQuerry(PreventionFilterDTO $filterDTO): array
    {
        $data = $filterDTO->toArray();
        $sql = "SELECT * FROM prevention_campaigns WHERE 1=1";
        $params = [];

        if (!empty($data['name'])) {
            $sql .= " AND campaign_name LIKE :name";
            $params['name'] = "%" . $data['name'] . "%";
        }

        $this->applyCommonFilters($sql, $params, $data, 'year', 'beneficiaries_count');
        return [$sql, $params];
    }

    private function buildActivitiesQuerry(PreventionFilterDTO $filterDTO): array
    {
        $data = $filterDTO->toArray();
        $sql = "SELECT * FROM prevention_activities WHERE 1=1";
        $params = [];

        if (!empty($data['setting'])) {
            $sql .= " AND setting LIKE :setting";
            $params['setting'] = "%" . $data['setting'] . "%";
        }

        if (!empty($data['beneficiary_type'])) {
            $sql .= " AND beneficiary_type LIKE :beneficiary_type";
            $params['beneficiary_type'] = "%" . $data['beneficiary_type'] . "%";
        }

        $this->applyCommonFilters($sql, $params, $data, 'year', 'beneficiaries_count');
        return [$sql, $params];
    }

    public function getProjects(PreventionFilterDTO $filterDTO): array
    {
        [$sql, $params] = $this->buildProjectsQuerry($filterDTO);
        $rows = $this->fetchAll($sql, $params);
        return array_map(fn($fow) => PreventionProjectDTO::fromArray($fow), $rows);
    }

    public function getProjectsPaginated(PreventionFilterDTO $filterDTO, int $perPage): array
    {
        [$sql, $params] = $this->buildProjectsQuerry($filterDTO);
        $total = $this->fetchCount($sql, $params);
        $this->applyPagination($sql, $params, $filterDTO->getPage(), $perPage);
        $rows = $this->fetchAll($sql, $params);
        return [
            'data'  => array_map(fn($row) => PreventionProjectDTO::fromArray($row), $rows),
            'total' => $total,
        ];
    }

    public function getCampaigns(PreventionFilterDTO $filterDTO): array
    {
        [$sql, $params] = $this->buildCampaignsQuerry($filterDTO);
        $rows = $this->fetchAll($sql, $params);
        return array_map(fn($row) => PreventionCampaignDTO::fromArray($row), $rows);
    }

    public function getCampaignsPaginated(PreventionFilterDTO $filterDTO, int $perPage): array
    {
        [$sql, $params] = $this->buildCampaignsQuerry($filterDTO);
        $total = $this->fetchCount($sql, $params);
        $this->applyPagination($sql, $params, $filterDTO->getPage(), $perPage);
        $rows = $this->fetchAll($sql, $params);
        return [
            'data'  => array_map(fn($row) => PreventionCampaignDTO::fromArray($row), $rows),
            'total' => $total,
        ];
    }

    public function getActivities(PreventionFilterDTO $filterDTO): array
    {
        [$sql, $params] = $this->buildActivitiesQuerry($filterDTO);
        $rows = $this->fetchAll($sql, $params);
        return array_map(fn($row) => PreventionActivityDTO::fromArray($row), $rows);
    }

    public function getActivitiesPaginated(PreventionFilterDTO $filterDTO, int $perPage): array
    {
        [$sql, $params] = $this->buildActivitiesQuerry($filterDTO);
        $total = $this->fetchCount($sql, $params);
        $this->applyPagination($sql, $params, $filterDTO->getPage(), $perPage);
        $rows = $this->fetchAll($sql, $params);
        return [
            'data'  => array_map(fn($row) => PreventionActivityDTO::fromArray($row), $rows),
            'total' => $total,
        ];
    }

    // optiunile pentru filtrare
    public function getProjectOptions(): array
    {
        return ['years' => $this->getDistinct('prevention_projects', 'year', 'DESC')];
    }

    public function getCampaignOptions(): array
    {
        return ['years' => $this->getDistinct('prevention_campaigns', 'year', 'DESC')];
    }

    public function getActivityOptions(): array
    {
        return [
            'years'     => $this->getDistinct('prevention_activities', 'year', 'DESC'),
            'ben_types' => $this->getDistinct('prevention_activities', 'beneficiary_type')
        ];
    }

    // CRUD: prevention_projects
    public function createProject(PreventionProjectCreateDTO $dto): PreventionProjectDTO
    {
        $sql = "INSERT INTO prevention_projects (year, project_name, beneficiaries_count)
                VALUES (:year, :project_name, :beneficiaries_count)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'year'                => $dto->getYear(),
            'project_name'        => $dto->getProjectName(),
            'beneficiaries_count' => $dto->getBeneficiariesCount(),
        ]);

        $row = $this->fetchById('prevention_projects', (int)$this->pdo->lastInsertId());
        if ($row === null) {
            throw new RuntimeException('Failed to fetch created project.');
        }
        return PreventionProjectDTO::fromArray($row);
    }

    public function updateProject(int $id, PreventionProjectUpdateDTO $dto): ?PreventionProjectDTO
    {
        if ($this->fetchById('prevention_projects', $id) === null) {
            return null;
        }
        $this->applyUpdate('prevention_projects', $id, $dto->toArray());

        $row = $this->fetchById('prevention_projects', $id);
        if ($row === null) {
            throw new RuntimeException('Failed to fetch updated project.');
        }
        return PreventionProjectDTO::fromArray($row);
    }

    public function deleteProject(int $id): bool
    {
        return $this->deleteById('prevention_projects', $id);
    }

    // CRUD: prevention_campaigns
    public function createCampaign(PreventionCampaignCreateDTO $dto): PreventionCampaignDTO
    {
        $sql = "INSERT INTO prevention_campaigns (year, campaign_name, beneficiaries_count)
                VALUES (:year, :campaign_name, :beneficiaries_count)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'year'                => $dto->getYear(),
            'campaign_name'       => $dto->getCampaignName(),
            'beneficiaries_count' => $dto->getBeneficiariesCount(),
        ]);

        $row = $this->fetchById('prevention_campaigns', (int)$this->pdo->lastInsertId());
        if ($row === null) {
            throw new RuntimeException('Failed to fetch created campaign.');
        }
        return PreventionCampaignDTO::fromArray($row);
    }

    public function updateCampaign(int $id, PreventionCampaignUpdateDTO $dto): ?PreventionCampaignDTO
    {
        if ($this->fetchById('prevention_campaigns', $id) === null) {
            return null;
        }
        $this->applyUpdate('prevention_campaigns', $id, $dto->toArray());

        $row = $this->fetchById('prevention_campaigns', $id);
        if ($row === null) {
            throw new RuntimeException('Failed to fetch updated campaign.');
        }
        return PreventionCampaignDTO::fromArray($row);
    }

    public function deleteCampaign(int $id): bool
    {
        return $this->deleteById('prevention_campaigns', $id);
    }

    // CRUD: prevention_activities
    public function createActivity(PreventionActivityCreateDTO $dto): PreventionActivityDTO
    {
        $sql = "INSERT INTO prevention_activities (year, setting, activities_count, beneficiaries_count, beneficiary_type)
                VALUES (:year, :setting, :activities_count, :beneficiaries_count, :beneficiary_type)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'year'                => $dto->getYear(),
            'setting'             => $dto->getSetting(),
            'activities_count'    => $dto->getActivitiesCount(),
            'beneficiaries_count' => $dto->getBeneficiariesCount(),
            'beneficiary_type'    => $dto->getBeneficiaryType(),
        ]);

        $row = $this->fetchById('prevention_activities', (int)$this->pdo->lastInsertId());
        if ($row === null) {
            throw new RuntimeException('Failed to fetch created activity.');
        }
        return PreventionActivityDTO::fromArray($row);
    }

    public function updateActivity(int $id, PreventionActivityUpdateDTO $dto): ?PreventionActivityDTO
    {
        if ($this->fetchById('prevention_activities', $id) === null) {
            return null;
        }
        $this->applyUpdate('prevention_activities', $id, $dto->toArray());

        $row = $this->fetchById('prevention_activities', $id);
        if ($row === null) {
            throw new RuntimeException('Failed to fetch updated activity.');
        }
        return PreventionActivityDTO::fromArray($row);
    }

    public function deleteActivity(int $id): bool
    {
        return $this->deleteById('prevention_activities', $id);
    }
}
