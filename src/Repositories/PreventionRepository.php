<?php
namespace App\Repositories;

use PDO;

class PreventionRepository extends BaseRepository {
    public function getProjects(array $filters): array {
        $sql = "SELECT * FROM prevention_projects WHERE 1=1";
        $params = [];

        if (!empty($filters['name'])) {
            $sql .= " AND project_name LIKE :name";
            $params['name'] = "%" . $filters['name'] . "%";
        }

        // metoda din parinte pentru aplicare filtre
        $this->applyCommonFilters($sql, $params, $filters, 'year', 'beneficiaries_count');

        // execut query-ul
        return $this->fetchAll($sql, $params);
    }

    public function getCampaigns(array $filters): array {
        $sql = "SELECT * FROM prevention_campaigns WHERE 1=1";
        $params = [];

        if (!empty($filters['name'])) {
            $sql .= " AND campaign_name LIKE :name";
            $params['name'] = "%" . $filters['name'] . "%";
        }

        $this->applyCommonFilters($sql, $params, $filters, 'year', 'beneficiaries_count');
        return $this->fetchAll($sql, $params);
    }

    public function getActivities(array $filters): array {
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
        return $this->fetchAll($sql, $params);
    }
}