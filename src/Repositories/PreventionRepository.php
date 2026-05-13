<?php
namespace App\Repositories;

use PDO;

class PreventionRepository extends BaseRepository {
    private function buildProjectsQuerry(array $filters): array {
        $sql = "SELECT * FROM prevention_projects WHERE 1=1";
        $params = [];

        if (!empty($filters['name'])) {
            $sql .= " AND project_name LIKE :name";
            $params['name'] = "%" . $filters['name'] . "%";
        }

        // metoda din parinte pentru aplicare filtre
        $this->applyCommonFilters($sql, $params, $filters, 'year', 'beneficiaries_count');

        return [$sql, $params];
    }

    private function buildCampaigndQuerry(array $filters): array {
        $sql = "SELECT * FROM prevention_campaigns WHERE 1=1";
        $params = [];

        if (!empty($filters['name'])) {
            $sql .= " AND campaign_name LIKE :name";
            $params['name'] = "%" . $filters['name'] . "%";
        }

        $this->applyCommonFilters($sql, $params, $filters, 'year', 'beneficiaries_count');
        return [$sql, $params];
    }

    private function buildActivitiesQuerry(array $filters): array {
        $sql = "SELECT * FROM prevention_activities WHERE 1=1";
        $params = [];

        if (!empty($filters['setting'])) {
            $sql .= " AND setting LIKE :setting";
            $params['setting'] = "%" . $filters['setting'] . "%";
        }

        if (!empty($filters['beneficiary_type'])) {
            $sql .= " AND beneficiary_type LIKE :beneficiary_type";
            $params['beneficiary_type'] = "%" . $filters['beneficiary_type'] . "%";
        }

        $this->applyCommonFilters($sql, $params, $filters, 'year', 'beneficiaries_count');
        return [$sql, $params];
    }

    public function getProjects(array $filters): array {
        [$sql, $params] = $this->buildProjectsQuerry($filters);
        return $this->fetchAll($sql, $params);
    }

    public function getProjectsPaginated(array $filters, int $page, int $perPage): array {
        [$sql, $params] = $this->buildProjectsQuerry($filters);
        $total = $this->fetchCount($sql, $params);
        $this->applyPagination($sql, $params, $page, $perPage);
        return [
            'data'  => $this->fetchAll($sql, $params),
            'total' => $total,
        ];;
    }

    public function getCampaigns(array $filters): array {
        [$sql, $params] = $this->buildCampaigndQuerry($filters);
        return $this->fetchAll($sql, $params);
    }

    public function getCampaignsPaginated(array $filters, int $page, int $perPage): array {
        [$sql, $params] = $this->buildCampaigndQuerry($filters);
        $total = $this->fetchCount($sql, $params);
        $this->applyPagination($sql, $params, $page, $perPage);
        return [
            'data'  => $this->fetchAll($sql, $params),
            'total' => $total,
        ];
    }

    public function getActivities(array $filters): array {
        [$sql, $params] = $this->buildActivitiesQuerry($filters);
        return $this->fetchAll($sql, $params);
    }

    public function getActivitiesPaginated(array $filters, int $page, int $perPage): array {
        [$sql, $params] = $this->buildActivitiesQuerry($filters);
        $total = $this->fetchCount($sql, $params);
        $this->applyPagination($sql, $params, $page, $perPage);
        return [
            'data'  => $this->fetchAll($sql, $params),
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