<?php

namespace App\DTOs\Crime;

class CrimeArticleUpdateDTO
{
    public function __construct(
        private readonly ?int $year = null,
        private readonly ?string $legalArticle = null,
        private readonly ?int $count = null,
    ) {}

    public static function fromRequest(array $params): self
    {
        return new self(
            year:         isset($params['year'])  ? (int)$params['year']  : null,
            legalArticle: $params['article']       ?? null,
            count:        isset($params['count']) ? (int)$params['count'] : null,
        );
    }

    public function getYear(): ?int
    {
        return $this->year;
    }
    public function getLegalArticle(): ?string
    {
        return $this->legalArticle;
    }
    public function getCount(): ?int
    {
        return $this->count;
    }

    public function toArray(): array
    {
        return array_filter([
            'year'          => $this->year,
            'legal_article' => $this->legalArticle,
            'count'         => $this->count,
        ], fn($value) => $value !== null);
    }
}
