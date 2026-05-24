<?php

namespace App\DTOs\Prevention;

class PreventionProjectUpdateDTO
{
    public function __construct(
        private readonly ?int $year = null,
        private readonly ?string $projectName = null,
        private readonly ?int $beneficiariesCount = null,
    ) {}

    public static function fromRequest(array $params): self
    {
        return new self(
            year:               isset($params['year'])  ? (int)$params['year']  : null,
            projectName:        $params['name']          ?? null,
            beneficiariesCount: isset($params['count']) ? (int)$params['count'] : null,
        );
    }

    public function getYear(): ?int
    {
        return $this->year;
    }
    public function getProjectName(): ?string
    {
        return $this->projectName;
    }
    public function getBeneficiariesCount(): ?int
    {
        return $this->beneficiariesCount;
    }

    public function toArray(): array
    {
        return array_filter([
            'year'                => $this->year,
            'project_name'        => $this->projectName,
            'beneficiaries_count' => $this->beneficiariesCount,
        ], fn($value) => $value !== null);
    }
}
