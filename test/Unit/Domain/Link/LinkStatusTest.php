<?php

namespace HyperfTest\Unit\Domain\Link;

use App\Domain\Link\LinkStatus;
use App\Domain\Link\RandomSlugGenerator;
use App\Domain\Link\Slug;
use PHPUnit\Framework\TestCase;

class LinkStatusTest extends TestCase
{
    public function test_active_tem_valor_active(): void
    {
        $this->assertSame("active", LinkStatus::ACTIVE->value);
    }

    public function test_expired_tem_valor_expired(): void
    {
        $this->assertSame("expired", LinkStatus::EXPIRED->value);
    }

    public function test_disabled_tem_valor_disabled(): void
    {
        $this->assertSame("disabled", LinkStatus::DISABLED->value);
    }

    public function test_from_string_valida(): void
    {
        $status = LinkStatus::from("active");

        $this->assertSame(LinkStatus::ACTIVE, $status);
    }

    public function test_from_string_invalida_lanca_excecao(): void
    {
        $this->expectException(\ValueError::class);

        $status = LinkStatus::from("test");
    }

}
