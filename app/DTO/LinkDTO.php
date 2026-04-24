<?php

namespace App\DTO;

use App\Domain\Link\LinkStatus;
use App\Model\Link;

class LinkDTO
{
    public function __construct(
        public string $slug,
        public string $originalUrl,
        public string $status = LinkStatus::ACTIVE->value,
        public ?string $expiresAt = null
    ) {}

    public static function fromModel(Link $link): self
    {
        return new self(
            slug: $link->slug,
            originalUrl: $link->original_url,
            status: $link->status,
            expiresAt: $link->expires_at,
        );
    }

    public function toArray(): array
    {
        return [
            'slug' => $this->slug,
            'original_url' => $this->originalUrl,
            'status' => $this->status,
            'expires_at' => $this->expiresAt,
        ];
    }
}