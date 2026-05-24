<?php
namespace App\DTOs\Emergency;

use App\DTOs\BaseFilterDTO;

class EmergencyFilterDTO extends BaseFilterDTO
{
    public function __construct(
        private readonly ?string $drugType   = null,
        private readonly ?string $category   = null,
        private readonly ?string $value      = null,
        private readonly ?int    $year       = null,
        private readonly ?int    $minYear    = null,
        private readonly ?int    $maxYear    = null,
        private readonly ?int    $count      = null,
        private readonly ?int    $minCount   = null,
        private readonly ?int    $maxCount   = null,
        private readonly ?int    $page       = null,
    ) {}

    // getteri pentru campurile comune
    public function getYear():      ?int { return $this->year; }
    public function getMinYear():   ?int { return $this->minYear; }
    public function getMaxYear():   ?int { return $this->maxYear; }
    public function getCount():     ?int { return $this->count; }
    public function getMinCount():  ?int { return $this->minCount; }
    public function getMaxCount():  ?int { return $this->maxCount; }
    public function getPage():      ?int { return $this->page; }

    // getteri pentru campurile specifice
    public function getDrugType(): ?string { return $this->drugType; }
    public function getCategory(): ?string { return $this->category; }
    public function getValue(): ?string { return $this->value; }

    // URL -> DTO (mascare nume parametri)
    public static function fromRequest(array $params): self
    {
        return new self(
            drugType: $params['drug']  ?? null,
            category: $params['type']  ?? null,
            value:    $params['val']   ?? null,
            year:     isset($params['year'])  ? (int)$params['year']  : null,
            minYear:  isset($params['from'])  ? (int)$params['from']  : null,
            maxYear:  isset($params['to'])    ? (int)$params['to']    : null,
            count:    isset($params['total']) ? (int)$params['total'] : null,
            minCount: isset($params['min'])   ? (int)$params['min']   : null,
            maxCount: isset($params['max'])   ? (int)$params['max']   : null,
            page:     isset($params['page'])  ? (int)$params['page']  : null,
        );
    }

    // DTO -> array cu coloane BD (folosit in repo)
    public function toArray(): array
    {
        return array_filter([
            'drug_type' => $this->drugType,
            'category'  => $this->category,
            'value'     => $this->value,
            'year'      => $this->year,
            'min_year'  => $this->minYear,
            'max_year'  => $this->maxYear,
            'count'     => $this->count,
            'min_count' => $this->minCount,
            'max_count' => $this->maxCount,
            'page'      => $this->page,
        ], fn($value) => $value !== null);
    }
}