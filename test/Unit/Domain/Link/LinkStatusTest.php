<?php

namespace HyperfTest\Unit\Domain\Link;

use App\Domain\Link\LinkStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class LinkStatusTest extends TestCase
{
    public static function status_e_valor_enum(): array
    {
        return [
            "active" => [LinkStatus::ACTIVE, "active"],
            "expired" => [LinkStatus::EXPIRED, "expired"],
            "disabled" => [LinkStatus::DISABLED, "disabled"],
        ];
    }

    #[DataProvider("status_e_valor_enum")]
    public function test_caso_tem_valor_correto(LinkStatus $status, string $valor): void
    {
        $this->assertSame($valor, $status->value);
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
