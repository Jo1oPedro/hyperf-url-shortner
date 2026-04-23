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
}
