<?php

declare(strict_types=1);

use function Hyperf\Support\env;

return [
    "algorithm" => env("JWT_ALGORITHM", "HS256"),
    "secret" => env("JWT_SECRET", "dev-secret-change-me-with-32-bytes-minimum"),
    "issuer" => env("JWT_ISSUER", "hyperf-url-shortner"),
    "audience" => env("JWT_AUDIENCE", ""),
    "ttl_seconds" => env("JWT_TTL_SECONDS", 3600),
];