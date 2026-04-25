<?php
namespace App\Repositories;

use PDO;

abstract class BaseRepository {
    protected PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // adaug filtrele comune
    protected function applyCommonFilters(string &$sql, array &$params, array $filters, string $yearCol = 'year', string $countCol = 'count'): void {
        if (!empty($filters['year'])) {
            $sql .= " AND $yearCol = :year";
            $params['year'] = $filters['year'];
        }
        if (!empty($filters['count'])) {
            $sql .= " AND $countCol = :cnt";
            $params['cnt'] = $filters['count'];
        }
        if (!empty($filters['min_count'])) {
            $sql .= " AND $countCol >= :min_cnt";
            $params['min_cnt'] = $filters['min_count'];
        }
        if (!empty($filters['max_count'])) {
            $sql .= " AND $countCol <= :max_cnt";
            $params['max_cnt'] = $filters['max_count'];
        }
    }

    // execut query-ul
    protected function fetchAll(string $sql, array $params = []): array {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}