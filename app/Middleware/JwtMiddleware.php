<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Domain\Auth\Claims;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;

class JwtMiddleware implements MiddlewareInterface
{
    public function __construct(
      private readonly ConfigInterface $config,
      private readonly HttpResponse $httpResponse,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $this->bearerToken($request);

        if($token === null) {
            return $this->unauthorized("Missing bearer token.");
        }

        try {
            $this->rejectAlgNone($token);

            $algorithm = (string) $this->config->get("jwt.algorithm", "HS256");
            $payload = JWT::decode($token, new Key($this->verificationKey(), $algorithm));

            $claims = $this->claimsFromPayload($payload);
            Context::set(Claims::CONTEXT_USER_ID, $claims->userId);
        } catch (ExpiredException) {
            return $this->unauthorized("Token expired.");
        } catch (\Throwable) {
            return $this->unauthorized("Invalid token.");
        }

        return $handler->handle($request);
    }

    private function bearerToken(ServerRequestInterface $request): ?string
    {
        $authorization = $request->getHeaderLine("Authorization");

        if(!preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches)) {
            return null;
        }

        return trim($matches[1]);
    }

    private function rejectAlgNone(string $token): void
    {
        $header = $this->decodeHeader($token);
        $algorithm = strtolower((string) $header["alg"] ?? "");

        if($algorithm === "none") {
            throw new \Exception("JWT alg none is not allowed");
        }

        if($algorithm !== strtolower((string) $this->config->get("jwt.algorithm", "HS256"))) {
            throw new \RuntimeException("JWT algorithm is not allowed");
        }
    }

    private function claimsFromPayload(object $payload): Claims
    {
        if(!isset($payload->exp)) {
            throw new \RuntimeException("Missing exp claim.");
        }

        if(($payload->iss ?? null) !== $this->config->get("jwt.issuer")) {
            throw new \RuntimeException("Invalid issuer.");
        }

        $audience = $payload->aud ?? null;
        $expectedAudience = $this->config->get("jwt.audience");

        if($audience !== $expectedAudience && !(is_array($audience) && in_array($expectedAudience, $audience, true))) {
            throw new \RuntimeException("Invalid audience.");
        }

        if(!isset($payload->user_id) || !is_string($payload->user_id) || $payload->user_id === "") {
            throw new \RuntimeException("Missing user_id claim.");
        }

        return new Claims(
            userId: $payload->user_id,
            issuedAt: (int) ($payload->iat ?? 0),
            expiresAt: (int) ($payload->exp ?? 0),
        );
    }

    private function verificationKey(): string
    {
        return (string) $this->config->get("jwt.secret");
    }

    private function decodeHeader(string $token): array
    {
        $segments = explode(".", $token);

        if(count($segments) !== 3) {
            throw new \RuntimeException("Malformed token.");
        }

        $json = base64_decode(strtr($segments[0], "-_", "+/"), true);

        $header = json_decode((string) $json, true);

        if(!is_array($header)) {
            throw new \RuntimeException("Invalid token header.");
        }

        return $header;
    }

    private function unauthorized(string $message): ResponseInterface
    {
        return $this->httpResponse
            ->json(["error" => $message])
            ->withStatus(401);
    }
}
