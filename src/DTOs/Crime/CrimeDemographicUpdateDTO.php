<?php

namespace App\DTOs\Crime;

class CrimeDemographicUpdateDTO
{
    public function __construct(
        private readonly ?int $year = null,
        private readonly ?string $gender = null,
        private readonly ?string $ageCategory = null,
        private readonly ?int $count = null,
    ) {}

    public static function fromRequest(array $params): self
    {
        return new self(
            year:        isset($params['year'])  ? (int)$params['year']  : null,
            gender:      $params['gender']        ?? null,
            ageCategory: $params['age']           ?? null,
            count:       isset($params['count']) ? (int)$params['count'] : null,
        );
    }

    public function getYear(): ?int
    {
        return $this->year;
    }
    public function getGender(): ?string
    {
        return $this->gender;
    }
    public function getAgeCategory(): ?string
    {
        return $this->ageCategory;
    }
    public function getCount(): ?int
    {
        return $this->count;
    }

    public function toArray(): array
    {
        return array_filter([
            'year'         => $this->year,
            'gender'       => $this->gender,
            'age_category' => $this->ageCategory,
            'count'        => $this->count,
        ], fn($value) => $value !== null);
    }
}
