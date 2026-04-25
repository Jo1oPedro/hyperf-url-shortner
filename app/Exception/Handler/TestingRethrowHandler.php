<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Exception\HttpException;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use function Hyperf\Support\env;

class TestingRethrowHandler extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        // não interrompe a propagação; outros handlers seguem rodando
        throw $throwable;
    }

    public function isValid(Throwable $throwable): bool
    {
        // só age em ambiente de teste e ignora exceções HTTP "esperadas"
        // (validation, not found, etc., que têm handler próprio)
        if (env('APP_ENV') !== 'testing') {
            return false;
        }

        return ! $throwable instanceof HttpException
            && ! $throwable instanceof \Hyperf\Validation\ValidationException
            && ! $throwable instanceof \App\Exception\SlugAlreadyExistsException
            && ! $throwable instanceof \App\Exception\LinkNotFoundException
            && ! $throwable instanceof \App\Exception\LinkGoneException;
    }
}