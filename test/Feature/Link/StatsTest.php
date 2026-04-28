<?php

namespace HyperfTest\Feature\Link;

use App\Domain\Link\LinkStatus;
use App\Model\Link;
use Carbon\Carbon;
use HyperfTest\HttpTestCase;

class StatsTest extends HttpTestCase
{
    protected function tearDown(): void
    {
        Link::query()->truncate();
        parent::tearDown();
    }

    public function test_retorna_stats_de_link_existente(): void
    {
        $this->post("/urls", [
            "url" => "https://example.com/",
            "slug" => "abc123"
        ]);
        Link::where('slug', 'abc123')->update(['clicks' => 3]);

        $response = $this->get("/urls/abc123/stats");

        $response->assertStatus(200);
        $response->assertJsonStructure(['slug', 'clicks', 'created_at', 'expires_at', 'status']);
        $this->assertEquals("abc123", $response->json('slug'));
        $this->assertEquals(3, $response->json('clicks'));
        $this->assertEquals(LinkStatus::ACTIVE->value, $response->json('status'));
        $this->assertNull($response->json("expires_at"));
    }

    public function test_retorna_404_quando_slug_nao_existe(): void
    {
        $response = $this->get("/urls/naoexiste/stats");

        $response->assertStatus(404);
    }

    public function test_retorna_stats_com_link_expired(): void
    {
        $this->post("/urls", ["url" => "https://example.com/", "slug" => "abc123"]);
        Link::where('slug', 'abc123')->update(['expires_at' => Carbon::yesterday()]);

        $response = $this->get("/urls/abc123/stats");

        $response->assertStatus(200);
        $this->assertEquals(LinkStatus::EXPIRED->value, $response->json('status'));
    }
}