<?php

namespace App\DTOs\Crime;

use App\DTOs\BaseFilterDTO;

class CrimeGeneralFilterDTO extends BaseFilterDTO
{
    public function __construct(
        private readonly ?int $year    = null,
        private readonly ?int $minYear = null,
        private readonly ?int $maxYear = null,
        private readonly ?int $page    = null,
    ) {}

    // getteri pentru campurile comune
    public function getYear():      ?int { return $this->year; }
    public function getMinYear():   ?int { return $this->minYear; }
    public function getMaxYear():   ?int { return $this->maxYear; }
    public function getPage():      ?int { return $this->page; }

    public static function fromRequest(array $params): self
    {
        return new self(
            year:    isset($params['year']) ? (int)$params['year'] : null,
            minYear: isset($params['from']) ? (int)$params['from'] : null,
            maxYear: isset($params['to'])   ? (int)$params['to']   : null,
            page:    isset($params['page']) ? (int)$params['page'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'year'     => $this->year,
            'min_year' => $this->minYear,
            'max_year' => $this->maxYear,
            'page'     => $this->page,
        ], fn($value) => $value !== null);
    }
}