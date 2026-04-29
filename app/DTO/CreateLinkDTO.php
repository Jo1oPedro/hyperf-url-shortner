<?php

namespace App\DTO;

final readonly class CreateLinkDTO
{
    public function __construct(
        public string $url,
        public ?string $slug = null,
        public ?string $expiresAt = null,
        public ?string $ownerUserId = null
    ) {}
}