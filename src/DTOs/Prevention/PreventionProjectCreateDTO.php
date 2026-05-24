<?php

namespace App\DTOs\Prevention;

class PreventionProjectCreateDTO
{
    public function __construct(
        private readonly ?int $year,
        private readonly ?string $projectName,
        private readonly ?int $beneficiariesCount,
    ) {}

    public static function fromRequest(array $params): self
    {
        return new self(
            year:               isset($params['year'])  ? (int)$params['year']  : null,
            projectName:        $params['name']          ?? null,
            beneficiariesCount: isset($params['count']) ? (int)$params['count'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'year'                => $this->getYear(),
            'project_name'        => $this->getProjectName(),
            'beneficiaries_count' => $this->getBeneficiariesCount(),
        ];
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
}
