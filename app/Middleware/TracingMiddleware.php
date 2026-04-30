<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Observability\ObservabilityContext;
use Hyperf\Context\Context;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\SpanKind;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class TracingMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if($request->getUri()->getPath() === "/metrics") {
            return $handler->handle($request);
        }

        $route = $this->routeLabel($request);
        $tracer = Globals::tracerProvider()->getTracer('hyperf-url-shortener');

        $span = $tracer
            ->spanBuilder($request->getMethod() . ' ' . $route)
            ->setSpanKind(SpanKind::KIND_SERVER)
            ->startSpan();

        $scope = $span->activate();

        try {
            $response = $handler->handle($request);

            $span->setAttribute('http.request.method', $request->getMethod());
            $span->setAttribute('http.route', $route);
            $span->setAttribute('http.response.status_code', $response->getStatusCode());
            $span->setAttribute('http.request.id', (string) Context::get(ObservabilityContext::REQUEST_ID));

            return $response;
        } catch (Throwable $throwable) {
            $span->recordException($throwable);

            throw $throwable;
        } finally {
            $scope->detach();
            $span->end();
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
