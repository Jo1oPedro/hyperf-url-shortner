<?php

declare(strict_types=1);

namespace App\Domain\Auth;

final readonly class Claims
{
    public const CONTEXT_USER_ID = "auth.user_id";

    public function __construct(
        public string $userId,
        public int $issuedAt,
        public int $expiresAt,
    ) {}
}