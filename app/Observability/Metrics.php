<?php

namespace App\Observability;

use Hyperf\Contract\ConfigInterface;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\Redis as PrometheusRedis;

class Metrics
{
    private CollectorRegistry $registry;

    public function __construct(ConfigInterface $config) {
        PrometheusRedis::setDefaultOptions([
            "host" => $config->get("observability.metrics_redis_host", "redis"),
            "port" => $config->get("observability.metrics_redis_port", 6379),
            "password" => $config->get("observability.metrics_redis_auth"),
            "timeout" => 0.1,
            "read_timeout" => 10,
            "persistent_connections" => false,
        ]);
        
        $this->registry = new CollectorRegistry(new PrometheusRedis());
    }

    public function observeHttpRequest(string $method, string $route, int $status, float $durationSeconds): void
    {
        $this->registry
            ->getOrRegisterCounter(
                "shortener",
                "http_requests_total",
                "Total HTTP requests.",
                ["method", "route", "status"]
            )
            ->inc([$method, $route, (string) $status]);
        
        $this->registry
            ->getOrRegisterHistogram(
                "shortener",
                "http_request_duration_seconds",
                "HTTP request duration in seconds.",
                ["method", "route", "status"],
                [0.005, 0.01, 0.025, 0.05, 0.1, 0.25, 0.5, 1, 2, 5]
            )
            ->observe($durationSeconds, [$method, $route, (string) $status]);
    }

    public function rateLimitBlocked(string $route): void
    {
        $this->registry
            ->getOrRegisterCounter(
                "shortener",
                "rate_limit_blocked_total",
                "Total requests blocked by rate limit.",
                ["route"]
            )
            ->inc([$route]);
    }

    public function jwtRejected(string $reasons): void
    {
        $this->registry
            ->getOrRegisterCounter(
                "shortener",
                "jwt_rejected_total",
                "Total rejected JWT tokens.",
                ["reason"]
            )
            ->inc([$reasons]);
    }

    public function linkCreated(): void
    {
        $this->registry
            ->getOrRegisterCounter(
                "shortener",
                "links_created_total",
                "Total created short links.",
            )
            ->inc();
    }

    public function redirectResolved(string $source): void
    {
        $this->registry
            ->getOrRegisterCounter(
                "shortener",
                "redirect_resolved_total",
                "Total successful redirects.",
                ["source"]
            )
            ->inc([$source]);
    }

    public function queueJobPushed(string $job): void
    {
        $this->registry
            ->getOrRegisterCounter(
                "shortener",
                "queue_job_pushed_total",
                "Total queued jobs pushed.",
                ["job"]
            )
            ->inc([$job]);
    }

    public function render(): string
    {
        return (new RenderTextFormat())->render(
            $this->registry->getMetricFamilySamples()
        );
    }

    public function contextType(): string
    {
        return RenderTextFormat::MIME_TYPE;
    }
}