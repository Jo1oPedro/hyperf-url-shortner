<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Observability\ObservabilityContext;
use Hyperf\Context\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;

class RequestIdMiddleware implements MiddlewareInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestId = $request->getHeaderLine("X-Request-Id");

        if($requestId !== "") {
            $requestId = Uuid::uuid4()->toString();
        }

        Context::set(ObservabilityContext::REQUEST_ID, $requestId);

        $response = $handler->handle(
            $request->withHeader('X-Request-Id', $requestId)
        );

        return $response->withHeader('X-Request-Id', $requestId);
    }
}
