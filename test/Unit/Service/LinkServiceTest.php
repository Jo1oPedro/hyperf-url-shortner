<?php

namespace HyperfTest\Unit\Service;

use App\Domain\Link\LinkRepository;
use App\Domain\Link\Slug;
use App\Domain\Link\SlugGenerator;
use App\Exception\SlugAlreadyExistsException;
use App\Service\LinkService;
use PHPUnit\Framework\TestCase;

class LinkServiceTest extends TestCase
{
    private LinkRepository $linkRepository;
    private SlugGenerator $slugGenerator;
    private LinkService $linkService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->linkRepository = $this->createMock(LinkRepository::class);
        $this->slugGenerator = $this->createMock(SlugGenerator::class);
        $this->linkService = new LinkService($this->linkRepository, $this->slugGenerator);
    }

    public function test_cria_link_com_slug_gerado(): void
    {
        $this->slugGenerator
            ->expects($this->once())
            ->method('generate')
            ->willReturn(new Slug("geradoslug"));

        $this->linkRepository
            ->expects($this->once())
            ->method('save');

        $link = $this->linkService->create("https://example.com");

        $this->assertSame("geradoslug", (string) $link->slug);
        $this->assertEquals("https://example.com", $link->original_url);
    }

    public function test_cria_link_com_slug_customizado(): void
    {
        $this->linkRepository
            ->expects($this->once())
            ->method("existsBySlug")
            ->willReturn(false);

        $this->linkRepository
            ->expects($this->once())
            ->method("save");

        $link = $this->linkService->create("https://example.com", "meulink");

        $this->assertSame("meulink", (string) $link->slug);
    }

    public function test_lanca_excecao_quando_url_invalida(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->linkService->create("nao-e-uma-url");
    }

    public function test_lanca_excecao_quando_slug_customizado_ja_existe(): void
    {
        $this->linkRepository
            ->expects($this->once())
            ->method("existsBySlug")
            ->willReturn(true);

        $this->expectException(SlugAlreadyExistsException::class);

        $this->linkService->create("https://example.com", "duplicado");
    }
}