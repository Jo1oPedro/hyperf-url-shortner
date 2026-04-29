<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Observability\Metrics;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HttpMetricsMiddleware implements MiddlewareInterface
{
    public function __construct(protected readonly Metrics $metrics) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $startedAt = microtime(true);
        $status = 500;

        try {
            $response = $handler->handle($request);
            $status = $response->getStatusCode();

            return $response;
        } catch (\Throwable $throwable) {
            throw $throwable;
        } finally {
            $this->metrics->observeHttpRequest(
                method: $request->getMethod(),
                route: $this->routeLabel($request),
                status: $status,
                durationSeconds: microtime(true) - $startedAt,
            );
        }
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
