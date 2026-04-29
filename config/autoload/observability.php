<?php

declare(strict_types=1);

use function Hyperf\Support\env;

return [
    "service_name" => env("SERVICE_NAME", "hyperf-url-shortener"),

    "metrics" => [
        "enabled" => (bool) env("METRIC_ENABLED", true),
        "redis_host" => env("REDIS_HOST", "redis"),
        "redis_port" => (int) env("REDIS_PORT", 6379),
        "redis_auth" => env("REDIS_AUTH", null),
        "redis_db" => (int) env("PROMETHEUS_REDIS_DB", 2),
    ]
];