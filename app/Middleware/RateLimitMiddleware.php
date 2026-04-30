<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Observability\Metrics;
use App\Observability\Tracer;
use Hyperf\Redis\Redis;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use OpenTelemetry\API\Trace\SpanInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;
use function Hyperf\Support\env;

class RateLimitMiddleware implements MiddlewareInterface
{
    private readonly int $limit;
    private readonly int $windowSeconds;
    private readonly bool $trustProxyHeaders;

    public function __construct(
        private readonly Redis $redis,
        private readonly HttpResponse $response,
        private readonly Metrics $metrics,
        private readonly Tracer $tracer,
    )
    {
        $this->limit = max(1, (int) env("RATE_LIMIT_MAX_REQUESTS", 100));
        $this->windowSeconds = max(1, (int) env("RATE_LIMIT_WINDOW_SECONDS", 60));
        $this->trustProxyHeaders = (bool) env("RATE_LIMIT_TRUST_PROXY_HEADERS", true);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if($request->getUri()->getPath() === '/metrics') {
            return $handler->handle($request);
        }

        $now = microtime(true);
        [$currentCount, $remaining, $resetAt] = $this->tracer->trace(
            "redis.rate_limit.check",
            function(SpanInterface $span) use($request, $handler, $now) {
                $key = "rl:" . $this->clientIp($request);
                $windowStart = $now - $this->windowSeconds;

                $this->redis->zRemRangeByScore($key, "-inf", (string) $windowStart);
                $count = (int) $this->redis->zCard($key);
                $this->redis->zAdd($key, $now, Uuid::uuid4()->toString());
                $this->redis->expire($key, $this->windowSeconds);

                $currentCount = $count + 1;
                $remaining = max(0, $this->limit - $currentCount);
                $resetAt = (int) ceil($this->oldestTimestamp($key, $now) + $this->windowSeconds);

                $span->setAttribute("rate_limit.limit", $this->limit);
                $span->setAttribute("rate_limit.remaining", $remaining);
                $span->setAttribute("rate_limit.allowed", $currentCount <= $this->limit);

                return [$currentCount, $remaining, $resetAt];
            },
            [
                "db.system" => "redis",
                "redis.operation" => "zset_sliding_window",
                "redis.key_pattern" => "rl:{ip}"
            ]
        );

        if($currentCount > $this->limit) {
            $retryAfter = max(1, $resetAt - (int) ceil($now));
            $this->metrics->rateLimitBlocked($this->routeLabel($request));

            return $this->withRateLimitHeaders(
                $this->response->json(["error" => "Rate limit exceeded."])
                    ->withStatus(429)
                    ->withHeader("Retry-After", (string) $retryAfter),
                $remaining,
                $resetAt
            );
        }

        return $this->withRateLimitHeaders(
            $handler->handle($request),
            $remaining,
            $resetAt
        );
    }

    private function withRateLimitHeaders(
        ResponseInterface $response,
        int $remaining,
        int $resetAt
    ): ResponseInterface {
        return $response
            ->withHeader("X-RateLimit-Limit", $this->limit)
            ->withHeader("X-RateLimit-Remaining", $remaining)
            ->withHeader("X-RateLimit-Reset", $resetAt);
    }

    private function oldestTimestamp(string $key, float $fallback): float
    {
        $oldest = $this->redis->zRange($key, 0, 0, true);

        if($oldest === false || $oldest === []) {
            return $fallback;
        }

        return (float) reset($oldest);
    }

    private function clientIp(ServerRequestInterface $request): string
    {
        if($this->trustProxyHeaders) {
            $forwardedIp = $this->firstValidForwardedIp($request->getHeaderLine('X-Forwarded-For'));

            if($forwardedIp !== null) {
                return $forwardedIp;
            }

            $realIp = $request->getHeaderLine('X-Real-IP');

            if($this->isValidIp($realIp)) {
                return $realIp;
            }
        }

        $remoteAddr = $request->getServerParams()["remote_addr"] ?? null;

        return is_string($remoteAddr) && $this->isValidIp($remoteAddr) ? $remoteAddr : "unknown";
    }

    private function firstValidForwardedIp(string $forwardedFor): ?string
    {
        foreach(explode(",", $forwardedFor) as $canditate)
        {
            $canditate = trim($canditate);

            if($this->isValidIp($canditate)) {
                return $canditate;
            }
        }

        return null;
    }

    private function isValidIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    private function routeLabel(ServerRequestInterface $request): string
    {
        $path = $request->getUri()->getPath();

        return match (true) {
            $path === '/' => '/',
            $path === '/metrics' => '/metrics',
            $path === '/urls' => '/urls',
            preg_match('#^/urls/[^/]+/stats$#', $path) === 1 => '/urls/{slug}/stats',
            preg_match('#^/[^/]+$#', $path) === 1 => '/{slug}',
            default => 'unknown',
        };
    }
}
