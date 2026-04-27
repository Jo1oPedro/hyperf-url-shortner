<?php

namespace App\Service;

use Hyperf\Redis\Redis;
use function Hyperf\Support\env;

class LinkCache
{
    private const PREFIX = "link:";
    private const NEGATIVE_PREFIX = "link:notfound:";
    private const NEGATIVE_TTL = 30;
    private int $ttl;

    public function __construct(private readonly Redis $redis)
    {
        $this->ttl = (int) env('LINK_CACHE_TTL', 600);
    }

    public function get(string $slug): ?array
    {
        $data = $this->redis->get(self::PREFIX . $slug);

        return $data ? json_decode($data, true) : null;
    }

    public function set(string $slug, array $data): void
    {
        $this->redis->setex(self::PREFIX . $slug, $this->ttl, json_encode($data));
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