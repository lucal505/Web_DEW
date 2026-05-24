<?php

namespace App\DTOs\Crime;

class CrimeSentenceUpdateDTO
{
    public function __construct(
        private readonly ?int $year = null,
        private readonly ?string $sentenceType = null,
        private readonly ?string $lawReference = null,
        private readonly ?int $count = null,
    ) {}

    public static function fromRequest(array $params): self
    {
        return new self(
            year:         isset($params['year']) ? (int)$params['year']  : null,
            sentenceType: $params['sentence'] ?? null,
            lawReference: $params['law'] ?? null,
            count:        isset($params['count']) ? (int)$params['count'] : null,
        );
    }

    public function getYear(): ?int
    {
        return $this->year;
    }
    public function getSentenceType(): ?string
    {
        return $this->sentenceType;
    }
    public function getLawReference(): ?string
    {
        return $this->lawReference;
    }
    public function getCount(): ?int
    {
        return $this->count;
    }

    public function toArray(): array
    {
        return array_filter([
            'year'          => $this->year,
            'sentence_type' => $this->sentenceType,
            'law_reference' => $this->lawReference,
            'count'         => $this->count,
        ], fn($value) => $value !== null);
    }
}
