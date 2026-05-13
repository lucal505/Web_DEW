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

        if (!empty($filters['min_year'])) {
            $sql .= " AND $yearCol >= :min_year";
            $params['min_year'] = $filters['min_year'];
        }
        if (!empty($filters['max_year'])) {
            $sql .= " AND $yearCol <= :max_year";
            $params['max_year'] = $filters['max_year'];
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

    protected function applyPagination(string &$sql, array &$params, int $page, int $perPage): void
    {
        $offset = ($page - 1) * $perPage;
        $sql .= " LIMIT :limit OFFSET :offset";
        $params['limit']  = $perPage;
        $params['offset'] = $offset;
    }

    // execut query-ul
    protected function fetchAll(string $sql, array $params = []): array {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // pentru optiunile de filtrare 
    protected function fetchColumn(string $sql, array $params = []): array {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // pentru paginare
    protected function fetchCount(string $sql, array $params): int
    {
        $countSql = preg_replace('/^SELECT .+ FROM /is', 'SELECT COUNT(*) FROM ', $sql);
        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    protected function getDistinct(string $table, string $column, string $sort = 'ASC'): array {
        // sanitizare input
        $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        $column = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
        $sort = strtoupper($sort) === 'DESC' ? 'DESC' : 'ASC';
        
        $sql = "SELECT DISTINCT $column FROM $table WHERE $column IS NOT NULL AND $column != '' ORDER BY $column $sort";
        return $this->fetchColumn($sql);
    }
}