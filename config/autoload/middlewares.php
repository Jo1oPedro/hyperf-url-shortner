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

use App\Middleware\HttpMetricsMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Middleware\RequestIdMiddleware;
use App\Middleware\RequestLogMiddleware;
use Hyperf\Validation\Middleware\ValidationMiddleware;

return [
    'http' => [
        RequestIdMiddleware::class,
        RequestLogMiddleware::class,
        HttpMetricsMiddleware::class,
        RateLimitMiddleware::class,
        ValidationMiddleware::class,
    ],
];
