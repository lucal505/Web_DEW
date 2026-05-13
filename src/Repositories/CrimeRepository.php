<?php
namespace App\Repositories;

class CrimeRepository extends BaseRepository {
    private function buildDemographicsQuerry(array $filters): array {
        $sql = "SELECT * FROM crimes_demographic WHERE 1=1";
        $params = [];

        if (!empty($filters['gender'])) {
            $sql .= " AND gender = :gen";
            $params['gen'] = $filters['gender']; 
        }
        if (!empty($filters['age_category'])) {
            $sql .= " AND age_category = :age";
            $params['age'] = $filters['age_category'];
        }

        // metoda din parinte pentru aplicare filtre
        $this->applyCommonFilters($sql, $params, $filters);

        // returnez sql-ul si parametrii pentru a putea adauga paginarea daca e cazul
        return [$sql, $params];
    }

    private function buildSentencesQuerry(array $filters): array {
        $sql = "SELECT * FROM crimes_sentence WHERE 1=1";
        $params = [];

        if (!empty($filters['law'])) {
            $sql .= " AND law_reference LIKE :law";
            $params['law'] = "%" . $filters['law'] . "%";
        }
        
        $this->applyCommonFilters($sql, $params, $filters);
        return [$sql, $params];
    }

    private function buildArticlesQuerry(array $filters): array {
        $sql = "SELECT * FROM crimes_article WHERE 1=1";
        $params = [];

        if (!empty($filters['law'])) {
            $sql .= " AND legal_article LIKE :law";
            $params['law'] = "%" . $filters['law'] . "%";
        }

        $this->applyCommonFilters($sql, $params, $filters);
        return [$sql, $params];
    }

    public function getDemographics(array $filters): array {
        [$sql, $params] = $this->buildDemographicsQuerry($filters);
        return $this->fetchAll($sql, $params);
    }

    public function getDemographicsPaginated(array $filters, int $page, int $perPage): array {
        [$sql, $params] = $this->buildDemographicsQuerry($filters);
        $total = $this->fetchCount($sql, $params);
        $this->applyPagination($sql, $params, $page, $perPage);
        return [
            'data'  => $this->fetchAll($sql, $params),
            'total' => $total,
        ];
    }

    public function getSentences(array $filters): array {
        [$sql, $params] = $this->buildSentencesQuerry($filters);
        return $this->fetchAll($sql, $params);
    }

    public function getSentencesPaginated(array $filters, int $page, int $perPage): array {
        [$sql, $params] = $this->buildSentencesQuerry($filters);
        $total = $this->fetchCount($sql, $params);
        $this->applyPagination($sql, $params, $page, $perPage);
        return [
            'data'  => $this->fetchAll($sql, $params),
            'total' => $total,
        ];
    }

    public function getArticles(array $filters): array {
        [$sql, $params] = $this->buildArticlesQuerry($filters);
        return $this->fetchAll($sql, $params);
    }

    public function getArticlesPaginated(array $filters, int $page, int $perPage): array {
        [$sql, $params] = $this->buildArticlesQuerry($filters);
        $total = $this->fetchCount($sql, $params);
        $this->applyPagination($sql, $params, $page, $perPage);
        return [
            'data'  => $this->fetchAll($sql, $params),
            'total' => $total,
        ];
    }

    // optiunile pentru filtrare
    public function getDemographicOptions(): array {
        return [
            'years' => $this->getDistinct('crimes_demographic', 'year', 'DESC'),
            'genders' => $this->getDistinct('crimes_demographic', 'gender'),
            'age_categories' => $this->getDistinct('crimes_demographic', 'age_category')
        ];
    }

    public function getSentenceOptions(): array {
        return [
            'years' => $this->getDistinct('crimes_sentence', 'year', 'DESC'),
            'sentence_types' => $this->getDistinct('crimes_sentence', 'sentence_type')
        ];
    }

    public function getArticleOptions(): array {
        return ['years' => $this->getDistinct('crimes_article', 'year', 'DESC')];
    }
}