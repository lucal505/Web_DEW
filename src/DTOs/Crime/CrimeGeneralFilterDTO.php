<?php

namespace App\DTOs\Crime;

class CrimeGeneralFilterDTO
{
    public function __construct(
        public readonly ?int $year    = null,
        public readonly ?int $minYear = null,
        public readonly ?int $maxYear = null,
        public readonly ?int $page    = null,
    ) {}

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
        ], fn($v) => $v !== null);
    }
}