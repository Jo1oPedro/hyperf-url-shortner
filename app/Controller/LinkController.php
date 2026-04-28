<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\CreateLinkDTO;
use App\Request\CreateLinkRequest;
use App\Resource\LinkStatsResource;
use App\Service\LinkService;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use function Hyperf\Support\env;

class LinkController
{
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
        ));

        $data = $link->toArray();
        $data["short_url"] = rtrim(env("APP_URL", "http://localhost:9501"), "/") . "/" . $link->slug;

        return $response
            ->withStatus(201)
            ->withHeader("Content-Type", "application/json")
            ->withBody(new SwooleStream(json_encode($data)));
    }

    public function redirect(RequestInterface $request, ResponseInterface $response, string $slug)
    {
        $link = $this->linkService->resolve($slug);

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
