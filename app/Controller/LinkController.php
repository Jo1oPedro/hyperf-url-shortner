<?php

declare(strict_types=1);

namespace App\Controller;

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

    public function create(RequestInterface $request, ResponseInterface $response)
    {
        $url = $request->input("url", "");
        $customSlug = $request->input("slug");

        try {
            $link = $this->linkService->create($url, $customSlug);
        } catch (\InvalidArgumentException $exception) {
            return $response
                ->withStatus(400)
                ->withHeader("Content-Type", "application/json")
                ->withBody(new SwooleStream(json_encode(["error" => $exception->getMessage()])));
        }

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
