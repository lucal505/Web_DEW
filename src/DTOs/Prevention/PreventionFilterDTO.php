<?php

namespace App\DTOs\Prevention;

use App\DTOs\BaseFilterDTO;

class PreventionFilterDTO extends BaseFilterDTO
{
    public function __construct(
        private readonly ?string $name              = null,
        private readonly ?string $setting           = null,
        private readonly ?string $beneficiaryType   = null,
        private readonly ?int    $year              = null,
        private readonly ?int    $minYear           = null,
        private readonly ?int    $maxYear           = null,
        private readonly ?int    $count             = null,
        private readonly ?int    $minCount          = null,
        private readonly ?int    $maxCount          = null,
        private readonly ?int    $page              = null,
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
    public function getName(): ?string { return $this->name; }
    public function getSetting(): ?string { return $this->setting; }
    public function getBeneficiaryType(): ?string { return $this->beneficiaryType; }

    public static function fromRequest(array $params): self
    {
        return new self(
            name: $params['name']    ?? null,
            setting: $params['set'] ?? null,
            beneficiaryType: $params['ben_type'] ?? null,
            year: isset($params['year'])  ? (int)$params['year']  : null,
            minYear: isset($params['from'])  ? (int)$params['from']  : null,
            maxYear: isset($params['to'])    ? (int)$params['to']    : null,
            count: isset($params['total']) ? (int)$params['total'] : null,
            minCount: isset($params['min'])   ? (int)$params['min']   : null,
            maxCount: isset($params['max'])   ? (int)$params['max']   : null,
            page: isset($params['page'])  ? (int)$params['page']  : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name'             => $this->name,
            'setting'          => $this->setting,
            'beneficiary_type' => $this->beneficiaryType,
            'year'             => $this->year,
            'min_year'         => $this->minYear,
            'max_year'         => $this->maxYear,
            'count'            => $this->count,
            'min_count'        => $this->minCount,
            'max_count'        => $this->maxCount,
            'page'             => $this->page,
        ], fn($v) => $v !== null);
    }
}
