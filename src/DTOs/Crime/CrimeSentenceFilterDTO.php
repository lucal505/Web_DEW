<?php

namespace App\DTOs\Crime;

class CrimeSentenceFilterDTO
{
    public function __construct(
        public readonly ?string $law      = null,
        public readonly ?int    $year     = null,
        public readonly ?int    $minYear  = null,
        public readonly ?int    $maxYear  = null,
        public readonly ?int    $count    = null,
        public readonly ?int    $minCount = null,
        public readonly ?int    $maxCount = null,
        public readonly ?int    $page     = null,
    ) {}

    public static function fromRequest(array $params): self
    {
        return new self(
            law:      $params['law']   ?? null,
            year:     isset($params['year'])  ? (int)$params['year']  : null,
            minYear:  isset($params['from'])  ? (int)$params['from']  : null,
            maxYear:  isset($params['to'])    ? (int)$params['to']    : null,
            count:    isset($params['total']) ? (int)$params['total'] : null,
            minCount: isset($params['min'])   ? (int)$params['min']   : null,
            maxCount: isset($params['max'])   ? (int)$params['max']   : null,
            page:     isset($params['page'])  ? (int)$params['page']  : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'law'       => $this->law,
            'year'      => $this->year,
            'min_year'  => $this->minYear,
            'max_year'  => $this->maxYear,
            'count'     => $this->count,
            'min_count' => $this->minCount,
            'max_count' => $this->maxCount,
            'page'      => $this->page,
        ], fn($v) => $v !== null);
    }
}