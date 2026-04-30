<?php

namespace App\Service;

use App\Observability\Tracer;
use Hyperf\Redis\Redis;
use OpenTelemetry\API\Trace\SpanInterface;
use function Hyperf\Support\env;

class LinkCache
{
    private const PREFIX = "link:";
    private const NEGATIVE_PREFIX = "link:notfound:";
    private const NEGATIVE_TTL = 30;
    private int $ttl;

    public function __construct(
        private readonly Redis $redis,
        private readonly Tracer $tracer,
    )
    {
        $this->ttl = (int) env('LINK_CACHE_TTL', 600);
    }

    public function get(string $slug): ?array
    {
        return $this->tracer->trace(
            "redis.link_cache.get",
            function (SpanInterface $span) use ($slug) {
                $data = $this->redis->get(self::PREFIX . $slug);

                $span->setAttribute('cache.hit', $data !== false && $data !== null);

                return $data ? json_decode($data, true) : null;
            },
            [
                "db.system" => "redis",
                "redis.operation" => "GET",
                "redis.key_pattern" => "link:{slug}"
            ]
        );
    }

    public function set(string $slug, array $data): void
    {
        $this->tracer->trace(
            "redis.link_cache.set",
            function () use ($slug, $data) {
                $this->redis->setex(self::PREFIX . $slug, $this->ttl, json_encode($data));
            },
            [
                "db.system" => "redis",
                "redis.operation" => "SETEX",
                "redis.key_pattern" => "link:{slug}"
            ]
        );
    }

    public function invalidate(string $slug): void
    {
        $this->redis->del(self::PREFIX . $slug);
        $this->redis->del(self::NEGATIVE_PREFIX . $slug);
    }

    public function isKnownMissing(string $slug): bool
    {
        return (bool) $this->redis->exists(self::NEGATIVE_PREFIX . $slug);
    }

    public function markMissing(string $slug): void
    {
        $this->redis->setex(self::NEGATIVE_PREFIX . $slug, self::NEGATIVE_TTL, "1");
    }
}