<?php

namespace App\DTOs\Prevention;

class PreventionCampaignCreateDTO
{
    public function __construct(
        private readonly ?int $year,
        private readonly ?string $campaignName,
        private readonly ?int $beneficiariesCount,
    ) {}

    public static function fromRequest(array $params): self
    {
        return new self(
            year:               isset($params['year'])  ? (int)$params['year']  : null,
            campaignName:       $params['name']          ?? null,
            beneficiariesCount: isset($params['count']) ? (int)$params['count'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'year'                => $this->getYear(),
            'campaign_name'       => $this->getCampaignName(),
            'beneficiaries_count' => $this->getBeneficiariesCount(),
        ];
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
}
