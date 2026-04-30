<?php

declare(strict_types=1);

namespace App\Controller;

use App\Observability\Metrics;
use Hyperf\HttpServer\Contract\ResponseInterface;

class MetricsController
{
    public function __construct(private readonly Metrics $metrics) {}

    public function index(ResponseInterface $response)
    {
        return $response
            ->raw($this->metrics->render())
            ->withHeader('Content-Type', $this->metrics->contextType());
    }
}
