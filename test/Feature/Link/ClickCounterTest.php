<?php

namespace HyperfTest\Feature\Link;

use App\Job\IncrementClicksJob;
use App\Model\Link;
use HyperfTest\HttpTestCase;

class ClickCounterTest extends HttpTestCase
{
    protected function tearDown(): void
    {
        Link::query()->truncate();
        parent::tearDown();
    }

    public function test_contador_incrementa_apos_redirects(): void
    {
        $link = Link::query()->create([
            "slug" => "test-click",
            "original_url" => "https://example.com",
            "short_url" => "https://example.com",
        ]);

        for($i = 0; $i < 5; $i++) {
            $job = new IncrementClicksJob("test-click");
            $job->handle();
        }

        $this->assertSame(5, $link->fresh()->clicks);
        $link->delete();
    }
}