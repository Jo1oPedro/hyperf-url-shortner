<?php

namespace HyperfTest\Feature\Link;

use App\Model\Link;
use HyperfTest\HttpTestCase;

class CreateLinkTest extends HttpTestCase
{
    protected function tearDown(): void
    {
        Link::query()->truncate();
        parent::tearDown();
    }

    public function test_cria_link_com_slug_gerado_automaticamente(): void
    {
        $response = $this->json("/urls", ["url" => "https://example.com"]);

        $response->assertStatus(201);
        $response->assertJsonStructure(["slug", "short_url", "original_url"]);
        $response->assertJson(["original_url" => "https://example.com"]);
    }

    public function test_cria_link_com_slug_customizado(): void
    {
        $response = $this->json("/urls", [
            "url" => "https://example.com",
            "slug" => "meuslug"
        ]);

        $response->assertStatus(201);
        $response->assertJson(["slug" => "meuslug"]);
    }

    public function test_retorna_422_quando_url_invalida(): void
    {
        $response = $this->json("/urls", ["url" => "nao-e-uma-url"]);

        $response->assertStatus(422);
    }

    public function test_retorna_400_quando_slug_customizado_ja_existe(): void
    {
        $this->json("/urls", ["url" => "https://example.com", "slug" => "meuslug"]);

        $response = $this->json("/urls", ["url" => "https://example.com", "slug" => "meuslug"]);

        $response->assertStatus(409);
    }
}