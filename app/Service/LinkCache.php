<?php

namespace App\Service;

use Hyperf\Redis\Redis;

class LinkCache
{
    private const PREFIX = "link";
    private const TTL = 600;

    public function __construct(private readonly Redis $redis) {}

    public function get(string $slug): ?array
    {
        $data = $this->redis->get(self::PREFIX . $slug);

        return $data ? json_decode($data, true) : null;
    }

    public function set(string $slug, array $data): void
    {
        $this->redis->setex(self::PREFIX . $slug, self::TTL, json_encode($data));
    }

    public function invalidate(string $slug): void
    {
        $this->redis->del(self::PREFIX . $slug);
    }
}