<?php

namespace App\Observability;

use OpenTelemetry\API\Globals;

class Tracer
{
    public function trace(string $name, callable $callback, array $attributes = []): mixed
    {
        $tracer = Globals::tracerProvider()->getTracer("hyperf-url-shortener");

        $span = $tracer->spanBuilder($name)->startSpan();

        foreach($attributes as $key => $value) {
            $span->setAttribute($key, $value);
        }

        $scope = $span->activate();

        try {
            return $callback($span);
        } catch (\Throwable $throwable) {
            $span->recordException($throwable);

            throw $throwable;
        } finally {
            $scope->detach();
            $span->end();
        }
    }
}