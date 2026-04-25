<?php

namespace HyperfTest\Unit\Service;

use App\Domain\Link\LinkRepository;
use App\Domain\Link\Slug;
use App\Domain\Link\SlugGenerator;
use App\DTO\CreateLinkDTO;
use App\DTO\LinkDTO;
use App\Exception\SlugAlreadyExistsException;
use App\Model\Link;
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

    private function makeLinkModel(string $slug, string $url): Link
    {
        $link = new Link();
        $link->slug = $slug;
        $link->original_url = $url;

        return $link;
    }

    public function test_cria_link_com_slug_gerado(): void
    {
        $this->slugGenerator
            ->expects($this->once())
            ->method('generate')
            ->willReturn(new Slug("geradoslug"));

        $this->linkRepository
            ->expects($this->once())
            ->method('save')
            ->willReturn($this->makeLinkModel('geradoslug', 'https://example.com'));

        $linkDto = $this->linkService->create(new CreateLinkDTO(url: "https://example.com"));

        $this->assertInstanceOf(LinkDTO::class, $linkDto);
        $this->assertSame("geradoslug", (string) $linkDto->slug);
        $this->assertEquals("https://example.com", $linkDto->originalUrl);
    }

    public function test_cria_link_com_slug_customizado(): void
    {
        $this->linkRepository
            ->expects($this->once())
            ->method("existsBySlug")
            ->willReturn(false);

        $this->linkRepository
            ->expects($this->once())
            ->method("save")
            ->willReturn($this->makeLinkModel('meulink', 'https://example.com'));

        $linkDto = $this->linkService->create(new CreateLinkDTO(
            url: "https://example.com",
            slug:"meulink"
        ));

        $this->assertInstanceOf(LinkDTO::class, $linkDto);
        $this->assertSame("meulink", (string) $linkDto->slug);
    }

    public function test_lanca_excecao_quando_url_invalida(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->linkService->create(new CreateLinkDTO(url: "nao-e-uma-url"));
    }

    public function test_lanca_excecao_quando_slug_customizado_ja_existe(): void
    {
        $this->linkRepository
            ->expects($this->once())
            ->method("existsBySlug")
            ->willReturn(true);

        $this->expectException(SlugAlreadyExistsException::class);

        $this->linkService->create(new CreateLinkDTO(
            url: "https://example.com", slug:"duplicado"
        ));
    }
}