<?php

declare(strict_types=1);

namespace App\Job;

use Hyperf\AsyncQueue\Job;
use Hyperf\DbConnection\Db;

class IncrementClicksJob extends Job
{
    public function __construct(
        private readonly string $slug
    ) {}

    public function handle()
    {
        Db::table('links')
            ->where('slug', $this->slug)
            ->increment('clicks');
    }
}
