<?php

namespace HyperfTest\Feature;

use Hyperf\Context\ApplicationContext;
use Hyperf\Redis\Redis;
use HyperfTest\HttpTestCase;

class RateLimitTest extends HttpTestCase
{
    protected function tearDown(): void
    {
        ApplicationContext::getContainer()
            ->get(Redis::class)
            ->del("rl:203.0.113.10");

        parent::tearDown();
    }

    public function test_bloqueia_request_101_na_mesma_janela_por_ip(): void
    {
        $headers = ["X-Forwarded-For" => "203.0.113.10"];

        for($i = 1; $i <= 100; $i++) {
            $response = $this->get("/", [], $headers);

            $response->assertStatus(200);
            $this->assertSame("100", $response->getHeaderLine("X-RateLimit-Limit"));
        }

        $response = $this->get("/", [], $headers);

        $response->assertStatus(429);
        $this->assertSame("0", $response->getHeaderLine("X-RateLimit-Remaining"));
        $this->assertNotSame("", $response->getHeaderLine("Retry-After"));
        $this->assertNotSame("", $response->getHeaderLine("X-RateLimit-Reset-At"));

    }
}