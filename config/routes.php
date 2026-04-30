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

use App\Middleware\JwtMiddleware;
use Hyperf\HttpServer\Router\Router;


Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController@index');

Router::get('/favicon.ico', function () {
    return '';
});

Router::addRoute(['POST'], '/urls', 'App\Controller\LinkController@create', ["middleware" => [JwtMiddleware::class]]);
Router::addRoute(['GET'], '/urls/{slug}/stats', 'App\Controller\LinkController@stats');
Router::get('/metrics', 'App\Controller\MetricsController@index');

Router::addRoute(['GET'], '/{slug}', 'App\Controller\LinkController@redirect');
