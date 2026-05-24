<?php

namespace App\DTOs\Crime;

class CrimeSentenceCreateDTO
{
    public function __construct(
        private readonly int $year,
        private readonly string $sentenceType,
        private readonly string $lawReference,
        private readonly ?int $count,
    ) {}

    public static function fromRequest(array $params): self
    {
        return new self(
            year:         isset($params['year'])  ? (int)$params['year']  : null,
            sentenceType: $params['sentence'] ?? null,
            lawReference: $params['law'] ?? null,
            count:        isset($params['count']) ? (int)$params['count'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'year'          => $this->getYear(),
            'sentence_type' => $this->getSentenceType(),
            'law_reference' => $this->getLawReference(),
            'count'         => $this->getCount(),
        ];
    }

    public function getYear(): int
    {
        return $this->year;
    }
    public function getSentenceType(): string
    {
        return $this->sentenceType;
    }
    public function getLawReference(): string
    {
        return $this->lawReference;
    }
    public function getCount(): ?int
    {
        return $this->count;
    }
}
