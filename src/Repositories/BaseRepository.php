<?php

namespace App\Repositories;

use PDO;

abstract class BaseRepository
{
    protected PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // aplic filtrele comune
    protected function applyCommonFilters(string &$sql, array &$params, array $filters, string $yearCol = 'year', string $countCol = 'count'): void
    {
        if (isset($filters['year']) && $filters['year'] !== null) {
            $sql .= " AND $yearCol = :year";
            $params['year'] = $filters['year'];
        }

        if (isset($filters['min_year']) && $filters['min_year'] !== null) {
            $sql .= " AND $yearCol >= :min_year";
            $params['min_year'] = $filters['min_year'];
        }
        if (isset($filters['max_year']) && $filters['max_year'] !== null) {
            $sql .= " AND $yearCol <= :max_year";
            $params['max_year'] = $filters['max_year'];
        }

        if (isset($filters['count']) && $filters['count'] !== null) {
            $sql .= " AND $countCol = :cnt";
            $params['cnt'] = $filters['count'];
        }

        if (isset($filters['min_count']) && $filters['min_count'] !== null) {
            $sql .= " AND $countCol >= :min_cnt";
            $params['min_cnt'] = $filters['min_count'];
        }
        if (isset($filters['max_count']) && $filters['max_count'] !== null) {
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
    protected function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // pentru paginare
    protected function fetchCount(string $sql, array $params): int
    {
        $countSql = preg_replace('/^SELECT .+ FROM /is', 'SELECT COUNT(*) FROM ', $sql);
        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    // pentru optiunile de filtrare 
    protected function fetchColumn(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    protected function getDistinct(string $table, string $column, string $sort = 'ASC'): array
    {
        $sort = strtoupper($sort) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT DISTINCT $column FROM $table WHERE $column IS NOT NULL AND $column != '' ORDER BY $column $sort";
        return $this->fetchColumn($sql);
    }

    // helperi generici pentru CRUD

    // intoarce randul cu id-ul dat sau null daca nu exista
    protected function fetchById(string $table, int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM $table WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    protected function insert(string $table, array $data): int
    {
        $columns      = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql  = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return (int)$this->pdo->lastInsertId();
    }

    // aplica un UPDATE doar pe coloanele primite in $data
    protected function update(string $table, int $id, array $data): void
    {
        if (empty($data)) {
            return; // nimic de actualizat
        }

        $sets = [];
        $params = ['id' => $id];
        foreach ($data as $column => $value) {
            $sets[] = "$column = :$column";
            $params[$column] = $value;
        }

        $sql = "UPDATE $table SET " . implode(', ', $sets) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    // sterge un rand dupa id
    protected function delete(string $table, int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM $table WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
