<?php

namespace App\DTOs\Prevention;

class PreventionCampaignUpdateDTO
{
    public function __construct(
        private readonly ?int $year = null,
        private readonly ?string $campaignName = null,
        private readonly ?int $beneficiariesCount = null,
    ) {}

    public static function fromRequest(array $params): self
    {
        return new self(
            year:               isset($params['year'])  ? (int)$params['year']  : null,
            campaignName:       $params['name']          ?? null,
            beneficiariesCount: isset($params['count']) ? (int)$params['count'] : null,
        );
    }

    public function getYear(): ?int
    {
        return $this->year;
    }
    public function getCampaignName(): ?string
    {
        return $this->campaignName;
    }
    public function getBeneficiariesCount(): ?int
    {
        return $this->beneficiariesCount;
    }

    public function toArray(): array
    {
        return array_filter([
            'year'                => $this->year,
            'campaign_name'       => $this->campaignName,
            'beneficiaries_count' => $this->beneficiariesCount,
        ], fn($value) => $value !== null);
    }
}
