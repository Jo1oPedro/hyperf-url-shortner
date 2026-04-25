<?php

namespace App\Exception\Handler;

use App\Exception\LinkNotFoundException;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Swow\Psr7\Message\ResponsePlusInterface;
use Throwable;

class LinkGoneHandler extends ExceptionHandler
{

    public function handle(Throwable $throwable, ResponsePlusInterface $response): ResponseInterface
    {
        $this->stopPropagation();

        return $response
            ->withStatus(410)
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new SwooleStream(json_encode(["error" => $throwable->getMessage()])));
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof LinkNotFoundException;
    }
}