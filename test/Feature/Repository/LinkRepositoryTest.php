<?php

namespace HyperfTest\Feature\Repository;

use App\Domain\Link\LinkRepository;
use App\Domain\Link\LinkStatus;
use App\Domain\Link\Slug;
use App\Model\Link;
use PHPUnit\Framework\TestCase;
use function Hyperf\Support\make;

class LinkRepositoryTest extends TestCase
{
    private LinkRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = make(LinkRepository::class);
    }

    protected function tearDown(): void
    {
        Link::query()->truncate();
        parent::tearDown();
    }

    private function criaLink(string $slug, string $url = "https://example.com"): Link
    {
        $link = new Link();
        $link->slug = $slug;
        $link->original_url = $url;
        $link->status = LinkStatus::ACTIVE->value;

        $this->repository->save($link);

        return $link;
    }

    public function test_salva_e_recupera_link_pelo_slug(): void
    {
        $link = $this->criaLink("meulink");

        $encontrado = $this->repository->findBySlug(new Slug($link->slug));

        $this->assertNotNull($encontrado);
        $this->assertSame("https://example.com", $encontrado->original_url);
    }

    public function test_exists_retorna_true_quando_slug_existe(): void
    {
        $link = $this->criaLink("meulink");

        $this->assertTrue($this->repository->existsBySlug(new Slug($link->slug)));
    }

    public function test_exist_retorna_false_quando_slug_nao_existe(): void
    {
        $this->assertFalse($this->repository->existsBySlug(new Slug("naoexiste")));
    }
}