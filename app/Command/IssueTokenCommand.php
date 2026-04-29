<?php

declare(strict_types=1);

namespace App\Command;

use Firebase\JWT\JWT;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\Contract\ConfigInterface;
use Symfony\Component\Console\Input\InputArgument;

#[Command]
class IssueTokenCommand extends HyperfCommand
{
    public function __construct(private readonly ConfigInterface $config)
    {
        parent::__construct('token:issue');
        $this->setDescription("Issue a test JWT token.");
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Hyperf Demo Command');
    }

    public function handle()
    {
        $now = time();
        $ttl = (int) $this->config->get("jwt.ttl_seconds", 3600);

        $token = JWT::encode([
           "iss" => $this->config->get("jwt.issuer"),
           "aud" => $this->config->get("jwt.audience"),
           "iat" => $now,
           "exp" => $now + $ttl,
           "user_id" => (string) $this->argument("user_id",)
        ],
            (string) $this->config->get("jwt.secret"),
            (string) $this->config->get("jwt.algorithm", "HS256")
        );

        $this->line($token);
        return self::SUCCESS;
    }

    protected function getArguments(): array
    {
        return [
            ["user_id", InputArgument::REQUIRED, "User Id claim."]
        ];
    }
}
