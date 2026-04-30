<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Domain\Auth\Claims;
use App\Observability\Metrics;
use App\Observability\Tracer;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use OpenTelemetry\API\Trace\SpanInterface;
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
        private readonly Metrics $metrics,
        private readonly Tracer $tracer,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $this->bearerToken($request);

        if($token === null) {
            $this->metrics->jwtRejected('missing_token');
            return $this->unauthorized("Missing bearer token.");
        }

        try {
            $claims = $this->tracer->trace(
                "jwt.validate",
                function (SpanInterface $span) use ($token) {
                    $this->rejectAlgNone($token);

                    $algorithm = (string) $this->config->get("jwt.algorithm", "HS256");

                    $span->setAttribute("jwt.algorithm", $algorithm);

                    $payload = JWT::decode($token, new Key($this->verificationKey(), $algorithm));

                    return $this->claimsFromPayload($payload);
                }
            );

            Context::set(Claims::CONTEXT_USER_ID, $claims->userId);
        } catch (ExpiredException) {
            $this->metrics->jwtRejected('token_expired');
            return $this->unauthorized("Token expired.");
        } catch (\Throwable) {
            $this->metrics->jwtRejected('invalid_token');
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
            $this->metrics->jwtRejected('jwt_alg_none_not_allowed');
            throw new \Exception("JWT alg none is not allowed");
        }

        if($algorithm !== strtolower((string) $this->config->get("jwt.algorithm", "HS256"))) {
            throw new \RuntimeException("JWT algorithm is not allowed");
        }
    }

    private function claimsFromPayload(object $payload): Claims
    {
        if(!isset($payload->exp)) {
            $this->metrics->jwtRejected('missing_exp_claim');
            throw new \RuntimeException("Missing exp claim.");
        }

        if(($payload->iss ?? null) !== $this->config->get("jwt.issuer")) {
            $this->metrics->jwtRejected('invalid_issuer');
            throw new \RuntimeException("Invalid issuer.");
        }

        $audience = $payload->aud ?? null;
        $expectedAudience = $this->config->get("jwt.audience");

        if($audience !== $expectedAudience && !(is_array($audience) && in_array($expectedAudience, $audience, true))) {
            $this->metrics->jwtRejected('invalid_audience');
            throw new \RuntimeException("Invalid audience.");
        }

        if(!isset($payload->user_id) || !is_string($payload->user_id) || $payload->user_id === "") {
            $this->metrics->jwtRejected('missing_user_id_claim');
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
            $this->metrics->jwtRejected('malformed_token');
            throw new \RuntimeException("Malformed token.");
        }

        $json = base64_decode(strtr($segments[0], "-_", "+/"), true);

        $header = json_decode((string) $json, true);

        if(!is_array($header)) {
            $this->metrics->jwtRejected('invalid_token_header');
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
