<?php

namespace App\DTOs\Emergency;

class EmergencyUpdateDTO
{
    public function __construct(
        private readonly ?int $year = null,
        private readonly ?string $drugType = null,
        private readonly ?string $category = null,
        private readonly ?string $value = null,
        private readonly ?int $count = null,
    ) {}

    public function getYear(): ?int
    {
        return $this->year;
    }
    public function getDrugType(): ?string
    {
        return $this->drugType;
    }
    public function getCategory(): ?string
    {
        return $this->category;
    }
    public function getValue(): ?string
    {
        return $this->value;
    }
    public function getCount(): ?int
    {
        return $this->count;
    }

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
        return array_filter([
            'year' => $this->year,
            'drug_type' => $this->drugType,
            'category' => $this->category,
            'value' => $this->value,
            'count' => $this->count,
        ], fn($value) => $value !== null);
    }
}
