<?php

declare(strict_types=1);

namespace HyperfTest\Unit\Domain\Link;

use App\Domain\Link\Slug;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SlugTest extends TestCase
{
    public function test_aceita_slug_valido(): void
    {
        $slug = new Slug('meu-link');

        $this->assertInstanceOf(Slug::class, $slug);
    }

    public function test_rejeita_slug_muito_curto(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Slug('abc');
    }

    public function test_rejeita_caractere_proibido(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Slug('slug@invalido');
    }

    public function test_rejeita_random_slug_muito_curto(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Slug::random(3);
    }

    public function test_rejeita_random_slug_muito_longo(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Slug::random(17);
    }

    public function test_aceita_random_slug_sem_tamanho_informado_valido(): void
    {
        $slug = Slug::random();

        $this->assertInstanceOf(Slug::class, $slug);
        $this->assertSame(7, strlen((string) $slug));
    }

    public function test_aceita_random_slug_com_tamanho_informado_valido(): void
    {
        $length = 12;
        $slug = Slug::random($length);

        $this->assertInstanceOf(Slug::class, $slug);
        $this->assertSame($length, strlen((string) $slug));
    }

    public function test_to_string_retorna_slug_valido(): void
    {
        $slug = Slug::random();

        $this->assertIsString((string) $slug);
    }
}
