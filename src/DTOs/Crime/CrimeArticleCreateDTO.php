<?php

namespace App\DTOs\Crime;

class CrimeArticleCreateDTO
{
    public function __construct(
        private readonly ?int $year,
        private readonly ?string $legalArticle,
        private readonly ?int $count,
    ) {}

    public static function fromRequest(array $params): self
    {
        return new self(
            year:         isset($params['year'])  ? (int)$params['year']  : null,
            legalArticle: $params['article']       ?? null,
            count:        isset($params['count']) ? (int)$params['count'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'year'          => $this->getYear(),
            'legal_article' => $this->getLegalArticle(),
            'count'         => $this->getCount(),
        ];
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
}
