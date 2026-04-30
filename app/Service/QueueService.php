<?php

namespace App\Service;

use App\Observability\Metrics;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\JobInterface;
use Hyperf\Cache\Driver\DriverInterface;

class QueueService
{
    /**
     * @var DriverInterface
     */
    protected $driver;

    public function __construct(
        DriverFactory $driverFactory,
        private readonly Metrics $metrics
    ) {
        $this->driver = $driverFactory;
    }

    public function push(JobInterface $job, int $delay = 0): bool
    {
        $pushed = $this->driver->get("default")->push($job, $delay);

        if($pushed) {
            $this->metrics->queueJobPushed(basename(str_replace('\\', '/', $job::class)));
        }

        return $pushed;
    }
}