<?php

namespace HyperfTest\Feature;

use App\Model\Link;
use Firebase\JWT\JWT;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Redis\Redis;
use HyperfTest\HttpTestCase;

class JwtMiddlewareTest extends HttpTestCase
{
    private const USER_ID = '123';
    private const IP = '198.51.100.91';

    protected function tearDown(): void
    {
        Link::query()->truncate();

        ApplicationContext::getContainer()
            ->get(Redis::class)
            ->del("rl:" . self::IP);

        parent::tearDown();
    }

    public function test_post_urls_retorna_401_sem_token(): void
    {
        $response = $this->json("/urls", [
           "url" => "https://example.com"
        ], $this->ipHeaders());

        $response->assertStatus(401);
        $response->assertJson(["error" => "Missing bearer token."]);
    }

    public function test_post_urls_retorna_401_com_token_invalido(): void
    {
        $response = $this->json("/urls", [
            "url" => "https://example.com"
        ], $this->authHeaders("token-invalido"));

        $response->assertStatus(401);
        $response->assertJson(["error" => "Invalid token."]);
    }

    public function test_post_urls_retorna_401_com_token_expirado(): void
    {
        $response = $this->json("/urls", [
            "url" => "https://example.com",
        ], $this->authHeaders($this->token(["exp" => time() - 60])));

        $response->assertStatus(401);
        $response->assertJson(["error" => "Token expired."]);
    }

    public function test_post_urls_retorna_401_com_alg_none(): void
    {
        $response = $this->json("/urls", [
            "url" => "https://example.com",
        ], $this->authHeaders($this->algNoneToken()));

        $response->assertStatus(401);
        $response->assertJson(["error" => "Invalid token."]);
    }

    public function test_post_urls_retorna_401_quando_token_nao_tem_exp(): void
    {
        $response = $this->json("/urls", [
            "url" => "https://example.com",
        ], $this->authHeaders($this->tokenWithoutExp()));

        $response->assertStatus(401);
        $response->assertJson(["error" => "Invalid token."]);
    }

    public function test_post_urls_cria_link_com_token_valido_e_salva_owner_user_id(): void
    {
        $response = $this->json("/urls", [
            "url" => "https://example.com",
            "slug" => "jwtok",
        ], $this->authHeaders($this->token()));

        $response->assertStatus(201);
        $response->assertJson(["slug" => "jwtok"]);

        $this->assertSame(
            self::USER_ID,
            Link::query()->where("slug", "jwtok")->first()->owner_user_id
        );
    }

    private function authHeaders(string $token): array
    {
        return $this->ipHeaders() + [
            "Authorization" => "Bearer $token",
        ];
    }

    private function ipHeaders(): array
    {
        return [
            "X-Forwarded-For" => self::IP,
        ];
    }

    private function token(array $overrides = []): string
    {
        $config = ApplicationContext::getContainer()->get(ConfigInterface::class);
        $now = time();

        $payload = array_merge([
            'iss' => $config->get('jwt.issuer'),
            'aud' => $config->get('jwt.audience'),
            'iat' => $now,
            'exp' => $now + 3600,
            'user_id' => self::USER_ID,
        ], $overrides);

        return JWT::encode(
            $payload, (string)
            $config->get('jwt.secret'),
            (string) $config->get("jwt.algorithm", "HS256")
        );
    }

    private function tokenWithoutExp(): string
    {
        $config = ApplicationContext::getContainer()->get(ConfigInterface::class);
        $now = time();

        return JWT::encode([
            'iss' => $config->get('jwt.issuer'),
            'aud' => $config->get('jwt.audience'),
            'iat' => $now,
            'user_id' => self::USER_ID,
        ],
            (string) $config->get('jwt.secret'),
            (string) $config->get('jwt.algorithm', 'HS256')
        );
    }

    private function algNoneToken(): string
    {
        $config = ApplicationContext::getContainer()->get(ConfigInterface::class);
        $now = time();

        $header = $this->base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => 'none']));
        $payload = $this->base64UrlEncode(json_encode([
            'iss' => $config->get('jwt.issuer'),
            'aud' => $config->get('jwt.audience'),
            'iat' => $now,
            'exp' => $now + 3600,
            'user_id' => self::USER_ID,
        ]));

        return $header . '.' . $payload . '.';
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}