<?php

namespace HyperfTest\Feature\Link;

use App\Domain\Link\LinkStatus;
use App\Model\Link;
use HyperfTest\HttpTestCase;

class RedirectTest extends HttpTestCase
{
    protected function tearDown(): void
    {
        Link::query()->truncate();
        parent::tearDown();
    }

    public function test_redireciona_para_url_original_quando_slug_existe(): void
    {
        Link::create([
            'slug' => 'meuslug',
            'original_url' => 'https://example.com',
        ]);

        $response = $this->get('/meuslug');

        $response->assertStatus(302);
    }

    public function test_retorna_404_quando_link_nao_existe(): void
    {
        $response = $this->get("/sluginexistente");

        $response->assertStatus(404);
    }

    public function test_retorna_410_quando_link_expirado(): void
    {
        Link::create([
            'slug' => 'expirado',
            'original_url' => 'https://example.com',
            'expires_at' => '2020-01-01 00:00:00',
        ]);

        $response = $this->get('/expirado');

        $response->assertStatus(410);
    }

    public function test_retorna_410_quando_link_inativo(): void
    {
        Link::create([
           "slug" => "inativo",
           "original_url" => "https://example.com",
           "status" => LinkStatus::DISABLED
        ]);

        $response = $this->get("/inativo");

        $response->assertStatus(410);
    }
}