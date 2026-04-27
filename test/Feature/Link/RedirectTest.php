<?php

namespace HyperfTest\Feature\Link;

use App\Controller\LinkController;
use App\Domain\Link\LinkRepository;
use App\Domain\Link\LinkStatus;
use App\Domain\Link\SlugGenerator;
use App\Infrastructure\Link\EloquentLinkRepository;
use App\Model\Link;
use App\Service\LinkCache;
use App\Service\LinkService;
use Hyperf\Context\ApplicationContext;
use HyperfTest\HttpTestCase;
use function Hyperf\Tappable\tap;

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

    public function test_segundo_request_nao_consulta_banco(): void
    {
        $container = ApplicationContext::getContainer();
        $linkCache = $container->get(LinkCache::class);
        $linkCache->invalidate("cached");

        $mockRepo = $this->createMock(LinkRepository::class);
        $mockRepo->expects($this->once())
            ->method("findBySlug")
            ->willReturn(tap(new Link(), function (Link $link) {
                $link->slug = "cached";
                $link->original_url = "https://example.com";
                $link->status = LinkStatus::ACTIVE->value;
                $link->expires_at = null;
            }));

        $mockService = new LinkService(
            $mockRepo,
            $container->get(SlugGenerator::class),
            $linkCache,
        );

        $container->set(LinkRepository::class, $mockRepo);
        $container->set(LinkService::class, $mockService);
        $container->set(LinkController::class, new LinkController($mockService));

        try {
            $this->get("/cached")->assertStatus(302);
            $this->get("/cached")->assertStatus(302);
        } finally {
            $linkCache->invalidate("cached");
            $realRepo = $container->get(EloquentLinkRepository::class);
            $realService = new LinkService(
                $realRepo,
                $container->get(SlugGenerator::class),
                $linkCache,
            );
            $container->set(LinkRepository::class, $realRepo);
            $container->set(LinkService::class, $realService);
            $container->set(LinkController::class, new LinkController($realService));
        }
    }
}