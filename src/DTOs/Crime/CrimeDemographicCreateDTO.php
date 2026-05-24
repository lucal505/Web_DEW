<?php

namespace App\DTOs\Crime;

class CrimeDemographicCreateDTO
{
    public function __construct(
        private readonly ?int $year,
        private readonly ?string $gender,
        private readonly ?string $ageCategory,
        private readonly ?int $count,
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

    public function toArray(): array
    {
        return [
            'year'         => $this->getYear(),
            'gender'       => $this->getGender(),
            'age_category' => $this->getAgeCategory(),
            'count'        => $this->getCount(),
        ];
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
}
