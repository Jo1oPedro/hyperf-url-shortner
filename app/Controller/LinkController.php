<?php

declare(strict_types=1);

namespace App\Controller;

use App\Request\CreateLinkRequest;
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
        $link = $this->linkService->create(
            $request->input("url"),
            $request->input("slug")
        );

        $shortUrl = rtrim(env("APP_URL", "http://localhost:9501"), "/") . "/" . $link->slug;

        return $response
            ->withStatus(201)
            ->withHeader("Content-Type", "application/json")
            ->withBody(new SwooleStream(json_encode([
                "slug" => $link->slug,
                "short_url" => $shortUrl,
                "original_url" => $link->original_url,
            ])));
    }
}
