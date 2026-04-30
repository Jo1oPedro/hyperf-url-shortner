<?php

namespace App\Service;

use App\Observability\Metrics;
use App\Observability\Tracer;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\JobInterface;
use Hyperf\Cache\Driver\DriverInterface;
use OpenTelemetry\API\Trace\SpanInterface;

class QueueService
{
    /**
     * @var DriverInterface
     */
    protected $driver;

    public function __construct(
        DriverFactory $driverFactory,
        private readonly Metrics $metrics,
        private readonly Tracer $tracer
    ) {
        $this->driver = $driverFactory;
    }

    public function push(JobInterface $job, int $delay = 0): bool
    {
        $jobName = basename(str_replace('\\', '/', $job::class));
        return $this->tracer->trace(
            "queue.async.push",
            function (SpanInterface $span) use ($jobName, $job, $delay) {
                $pushed = $this->driver->get("default")->push($job, $delay);

                if($pushed) {
                    $this->metrics->queueJobPushed($jobName);
                }

                $span->setAttribute("queue.pushed", $pushed);

                return $pushed;
            },
            [
                "messaging.system" => "hyperf_async_queue",
                "messaging.destination" => "default",
                "job.name" => $jobName,
                "job.delay_seconds" => $delay,
            ]
        );
    }
}