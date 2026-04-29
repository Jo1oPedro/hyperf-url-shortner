<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Observability\ObservabilityContext;
use Hyperf\Context\Context;
use Hyperf\Logger\LoggerFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class RequestLogMiddleware implements MiddlewareInterface
{
    private LoggerInterface $logger;
    public function __construct(LoggerFactory $loggerFactory)
    {
        $this->logger = $loggerFactory->get("http");
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $startedAt = microtime(true);

        try {
            $response = $handler->handle($request);

            $this->logger->info("http_request", [
                "request_id" => Context::get(ObservabilityContext::REQUEST_ID),
                "method" => $request->getMethod(),
                "path" => $request->getUri()->getPath(),
                "route" => $this->routeLabel($request),
                "status" => $response->getStatusCode(),
                "duration_ms" => round((microtime(true) - $startedAt) * 1000, 2),
            ]);

            return $response;
        } catch (\Throwable $throwable) {
            $this->logger->error("http_request_failed", [
                "request_id" => Context::get(ObservabilityContext::REQUEST_ID),
                "method" => $request->getMethod(),
                "path" => $request->getUri()->getPath(),
                "route" => $this->routeLabel($request),
                "duration_ms" => round((microtime(true) - $startedAt) * 1000, 2),
                "exception" => $throwable::class,
                "message" => $throwable->getMessage(),
            ]);

            throw $throwable;
        }
    }

    private function routeLabel(ServerRequestInterface $request): string
    {
        $path = $request->getUri()->getPath();

        return match (true) {
            $path === "/" => "/",
            $path === '/metrics' => '/metrics',
            $path === '/urls' => '/urls',
            preg_match('#^/urls/[^/]+/stats$#', $path) === 1 => '/urls/{slug}/stats',
            preg_match('#^/[^/]+$#', $path) === 1 => '/{slug}',
            default => 'unknown',
        };
    }
}
