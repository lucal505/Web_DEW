<?php

namespace App\DTOs\Prevention;

class PreventionActivityUpdateDTO
{
    public function __construct(
        private readonly ?int $year = null,
        private readonly ?string $setting = null,
        private readonly ?int $activitiesCount = null,
        private readonly ?int $beneficiariesCount = null,
        private readonly ?string $beneficiaryType = null,
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

    public function getYear(): ?int
    {
        return $this->year;
    }
    public function getSetting(): ?string
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

    public function toArray(): array
    {
        return array_filter([
            'year'                => $this->year,
            'setting'             => $this->setting,
            'activities_count'    => $this->activitiesCount,
            'beneficiaries_count' => $this->beneficiariesCount,
            'beneficiary_type'    => $this->beneficiaryType,
        ], fn($value) => $value !== null);
    }
}
