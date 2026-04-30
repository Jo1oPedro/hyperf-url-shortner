<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Auth\Claims;
use App\DTO\CreateLinkDTO;
use App\Observability\Metrics;
use App\Request\CreateLinkRequest;
use App\Resource\LinkStatsResource;
use App\Service\LinkService;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use function Hyperf\Support\env;

class LinkController
{
    #[Inject]
    private readonly Metrics $metrics;

    public function __construct(
        private readonly LinkService $linkService,
    ) {}

    public function index(RequestInterface $request, ResponseInterface $response)
    {
        return $response->raw('Hello Hyperf!');
    }

    public function create(CreateLinkRequest $request, ResponseInterface $response)
    {
        $link = $this->linkService->create(new CreateLinkDTO(
            url: $request->input("url"),
            slug: $request->input("slug"),
            expiresAt: $request->input("expires_at"),
            ownerUserId: Context::get(Claims::CONTEXT_USER_ID)
        ));

        $data = $link->toArray();
        $data["short_url"] = rtrim(env("APP_URL", "http://localhost:9501"), "/") . "/" . $link->slug;

        $this->metrics->linkCreated();

        return $response
            ->withStatus(201)
            ->withHeader("Content-Type", "application/json")
            ->withBody(new SwooleStream(json_encode($data)));
    }

    public function redirect(RequestInterface $request, ResponseInterface $response, string $slug)
    {
        $link = $this->linkService->resolve($slug);

        // change success to redis or database in the future
        $this->metrics->redirectResolved("success");

        return $response
            ->withStatus(302)
            ->withHeader("Location", $link->originalUrl);
    }

    public function stats(RequestInterface $request, ResponseInterface $response, string $slug)
    {
        $link = $this->linkService->linkStats($slug);
        $resource = new LinkStatsResource($link);

        return $response->json($resource->toArray());
    }
}
