<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

use App\Exception\Handler\LinkGoneHandler;
use App\Exception\Handler\LinkNotFoundHandler;
use App\Exception\Handler\SlugAlreadyExistsHandler;
use App\Exception\Handler\TestingRethrowHandler;
use Hyperf\Validation\ValidationExceptionHandler;

return [
    'handler' => [
        'http' => [
            TestingRethrowHandler::class,
            Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler::class,
            ValidationExceptionHandler::class,
            SlugAlreadyExistsHandler::class,
            LinkGoneHandler::class,
            LinkNotFoundHandler::class,
            App\Exception\Handler\AppExceptionHandler::class,
        ],
    ],
];
