<?php

namespace App\DTOs\Emergency;

class EmergencyCreateDTO
{
    public function __construct(
        private readonly int $year,
        private readonly string $drugType,
        private readonly string $category,
        private readonly string $value,
        private readonly int $count,
    ) {}

    public static function fromRequest(array $params): self
    {
        return new self(
            year:     isset($params['year'])  ? (int)$params['year']  : null,
            drugType: $params['drug']         ?? null,
            category: $params['type']         ?? null,
            value:    $params['val']          ?? null,
            count:    isset($params['count']) ? (int)$params['count'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'year'      => $this->getYear(),
            'drug_type' => $this->getDrugType(),
            'category'  => $this->getCategory(),
            'value'     => $this->getValue(),
            'count'     => $this->getCount(),
        ];
    }

    public function getYear(): int
    {
        return $this->year;
    }
    public function getDrugType(): string
    {
        return $this->drugType;
    }
    public function getCategory(): string
    {
        return $this->category;
    }
    public function getValue(): string
    {
        return $this->value;
    }
    public function getCount(): int
    {
        return $this->count;
    }
}
