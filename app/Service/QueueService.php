<?php

namespace App\Service;

use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\JobInterface;
use Hyperf\Cache\Driver\DriverInterface;

class QueueService
{
    /**
     * @var DriverInterface
     */
    protected $driver;

    public function __construct(DriverFactory $driverFactory)
    {
        $this->driver = $driverFactory;
    }

    public function push(JobInterface $job, int $delay = 0): bool
    {
        return $this->driver->get("default")->push($job, $delay);
    }
}