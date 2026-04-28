<?php

namespace App\Resource;

use App\DTO\LinkDTO;

final readonly class LinkStatsResource
{
    public function __construct(
        private LinkDTO $linkDTO
    ) {}

    public function toArray(): array
    {
        return [
            'slug'       => $this->linkDTO->slug,
            'clicks'     => $this->linkDTO->clicks,
            'created_at' => $this->linkDTO->createdAt?->toIso8601String(),
            'expires_at' => $this->linkDTO->expiresAt?->toIso8601String(),
            'status'     => $this->linkDTO->status,
        ];
    }
}