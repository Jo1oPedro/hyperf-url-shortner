<?php

declare(strict_types=1);

namespace HyperfTest\Unit\Domain\Link;

use App\Domain\Link\Slug;
use App\Domain\Link\RandomSlugGenerator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class RandomSlugGeneratorTest extends TestCase
{
    public function test_generate_retorna_slug_valido(): void
    {
        $slug = new RandomSlugGenerator()->generate();

        $this->assertInstanceOf(Slug::class, $slug);
    }

    public function test_generate_return_slug_muito_curto_invalido(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $slug = new RandomSlugGenerator()->generate(3);
    }

    public function test_generate_return_slug_muito_longo_invalido(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $slug = new RandomSlugGenerator()->generate(17);
    }
}
