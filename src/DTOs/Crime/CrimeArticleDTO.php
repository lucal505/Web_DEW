<?php

namespace App\DTOs\Crime;

class CrimeArticleDTO implements \JsonSerializable
{
    public function __construct(
        public readonly int     $id,
        public readonly int     $year,
        public readonly string  $legalArticle,
        public readonly ?int    $count,
    ) {}

    public static function fromArray(array $row): self
    {
        return new self(
            id:           (int)$row['id'],
            year:         (int)$row['year'],
            legalArticle: $row['legal_article'],
            count:        isset($row['count']) ? (int)$row['count'] : null,
        );
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'id'      => $this->id,
            'year'    => $this->year,
            'article' => $this->legalArticle,
            'count'   => $this->count,
        ], fn($value) => $value !== null);
    }
}