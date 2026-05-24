<?php

namespace App\DTOs\Prevention;

class PreventionActivityCreateDTO
{
    public function __construct(
        private readonly int $year,
        private readonly string $setting,
        private readonly ?int $activitiesCount,
        private readonly ?int $beneficiariesCount,
        private readonly ?string $beneficiaryType,
    ) {}

    public static function fromRequest(array $params): self
    {
        return new self(
            year:               isset($params['year'])       ? (int)$params['year']       : null,
            setting:            $params['set']                ?? null,
            activitiesCount:    isset($params['activities'])  ? (int)$params['activities'] : null,
            beneficiariesCount: isset($params['count'])       ? (int)$params['count']      : null,
            beneficiaryType:    $params['ben_type']           ?? null,
        );
    }

    public function getYear(): int
    {
        return $this->year;
    }
    public function getSetting(): string
    {
        return $this->setting;
    }
    public function getActivitiesCount(): ?int
    {
        return $this->activitiesCount;
    }
    public function getBeneficiariesCount(): ?int
    {
        return $this->beneficiariesCount;
    }
    public function getBeneficiaryType(): ?string
    {
        return $this->beneficiaryType;
    }
}
