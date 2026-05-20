<?php
namespace App\Repositories;

use App\DTOs\Prevention\PreventionFilterDTO;
use App\DTOs\Prevention\PreventionProjectDTO;
use App\DTOs\Prevention\PreventionCampaignDTO;
use App\DTOs\Prevention\PreventionActivityDTO;

class PreventionRepository extends BaseRepository {
    private function buildProjectsQuerry(PreventionFilterDTO $filterDTO): array {
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

    private function buildCampaignsQuerry(PreventionFilterDTO $filterDTO): array {
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

    private function buildActivitiesQuerry(PreventionFilterDTO $filterDTO): array {
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

    public function getProjects(PreventionFilterDTO $filterDTO): array {
        [$sql, $params] = $this->buildProjectsQuerry($filterDTO);
        return $this->fetchAll($sql, $params);
    }

    public function getProjectsPaginated(PreventionFilterDTO $filterDTO, int $perPage): array {
        [$sql, $params] = $this->buildProjectsQuerry($filterDTO);
        $total = $this->fetchCount($sql, $params);
        $this->applyPagination($sql, $params, $filterDTO->page, $perPage);
        $rows = $this->fetchAll($sql, $params);
        return [
            'data'  => array_map(fn($row) => PreventionProjectDTO::fromArray($row), $rows),
            'total' => $total,
        ];
    }

    public function getCampaigns(PreventionFilterDTO $filterDTO): array {
        [$sql, $params] = $this->buildCampaignsQuerry($filterDTO);
        $rows = $this->fetchAll($sql, $params);
        return array_map(fn($row) => PreventionCampaignDTO::fromArray($row), $rows);    }

    public function getCampaignsPaginated(PreventionFilterDTO $filterDTO, int $perPage): array {
        [$sql, $params] = $this->buildCampaignsQuerry($filterDTO);
        $total = $this->fetchCount($sql, $params);
        $this->applyPagination($sql, $params, $filterDTO->page, $perPage);
        $rows = $this->fetchAll($sql, $params);
        return [
            'data'  => array_map(fn($row) => PreventionCampaignDTO::fromArray($row), $rows),
            'total' => $total,
        ];
    }

    public function getActivities(PreventionFilterDTO $filterDTO): array {
        [$sql, $params] = $this->buildActivitiesQuerry($filterDTO);
        $rows = $this->fetchAll($sql, $params);
        return array_map(fn($row) => PreventionActivityDTO::fromArray($row), $rows);
    }

    public function getActivitiesPaginated(PreventionFilterDTO $filterDTO, int $perPage): array {
        [$sql, $params] = $this->buildActivitiesQuerry($filterDTO);
        $total = $this->fetchCount($sql, $params);
        $this->applyPagination($sql, $params, $filterDTO->page, $perPage);
        $rows = $this->fetchAll($sql, $params);
        return [
            'data'  => array_map(fn($row) => PreventionActivityDTO::fromArray($row), $rows),
            'total' => $total,
        ];
    }

    // optiunile pentru filtrare
    public function getProjectOptions(): array {
        return ['years' => $this->getDistinct('prevention_projects', 'year', 'DESC')];
    }

    public function getCampaignOptions(): array {
        return ['years' => $this->getDistinct('prevention_campaigns', 'year', 'DESC')];
    }
    
    public function getActivityOptions(): array {
        return [
            'years' => $this->getDistinct('prevention_activities', 'year', 'DESC'),
            'beneficiary_types' => $this->getDistinct('prevention_activities', 'beneficiary_type')
        ];
    }
}