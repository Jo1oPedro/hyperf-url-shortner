<?php

namespace App\Exception\Handler;

use App\Exception\SlugAlreadyExistsException;
use Fig\Http\Message\StatusCodeInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Swow\Psr7\Message\ResponsePlusInterface;
use Throwable;

class SlugAlreadyExistsHandler extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponsePlusInterface $response)
    {
        $this->stopPropagation();

        return $response
            ->withStatus(StatusCodeInterface::STATUS_CONFLICT)
            ->withHeader('Content-type', 'application/json')
            ->withBody(new SwooleStream(json_encode(["error" => $throwable->getMessage()])));
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof SlugAlreadyExistsException;
    }
}