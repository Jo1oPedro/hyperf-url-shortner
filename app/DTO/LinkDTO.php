<?php

declare(strict_types=1);

namespace App\DTO;

use App\Domain\Link\LinkStatus;
use App\Model\Link;
use Carbon\Carbon;

class LinkDTO
{
    public function __construct(
        public string $slug,
        public string $originalUrl,
        public string $status = LinkStatus::ACTIVE->value,
        public ?Carbon $expiresAt = null,
        public int $clicks = 0,
        public ?Carbon $createdAt = null,
    ) {}

    public static function fromModel(Link $link): self
    {
        $status = $link->status;
        if ($status === LinkStatus::ACTIVE->value && $link->expires_at?->isPast()) {
            $status = LinkStatus::EXPIRED->value;
        }

        return new self(
            slug: $link->slug,
            originalUrl: $link->original_url,
            status: $status,
            expiresAt: $link->expires_at,
            clicks: (int) ($link->clicks ?? 0),
            createdAt: $link->created_at,
        );
    }

    public function toArray(): array
    {
        return [
            'slug' => $this->slug,
            'original_url' => $this->originalUrl,
            'status' => $this->status,
            'expires_at' => $this->expiresAt?->toIso8601String(),
        ];
    }
}