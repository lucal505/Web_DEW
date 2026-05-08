<?php
namespace App\Repositories;

class CrimeRepository extends BaseRepository {
    public function getDemographics(array $filters): array {
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

        // execut query-ul
        return $this->fetchAll($sql, $params);
    }

    public function getSentences(array $filters): array {
        $sql = "SELECT * FROM crimes_sentence WHERE 1=1";
        $params = [];

        if (!empty($filters['law'])) {
            $sql .= " AND law_reference LIKE :law";
            $params['law'] = "%" . $filters['law'] . "%";
        }
        
        $this->applyCommonFilters($sql, $params, $filters);
        return $this->fetchAll($sql, $params);
    }

    public function getArticles(array $filters): array {
        $sql = "SELECT * FROM crimes_article WHERE 1=1";
        $params = [];

        if (!empty($filters['law'])) {
            $sql .= " AND legal_article LIKE :law";
            $params['law'] = "%" . $filters['law'] . "%";
        }

        $this->applyCommonFilters($sql, $params, $filters);
        return $this->fetchAll($sql, $params);
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