<?php

declare(strict_types=1);

namespace HyperfTest\Unit\Domain\Link;

use App\Domain\Link\Slug;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SlugTest extends TestCase
{
    public static function slugs_invalidos(): array
    {
        return [
            "muito curto" => ["abc"],
            "muito longo" => ["slug-com-dezessete-c"],
            "com arroba" => ["slug@invalido"],
            "com espaco" => ["slug invalido"],
            "com ponto" => ["slug.invalido"],
        ];
    }

    public static function random_slugs_invalidos(): array
    {
        return [
            "muito longo" => [17],
            "muito curto" => [3]
        ];
    }

    public function test_aceita_slug_valido(): void
    {
        $slug = new Slug('meu-link');

        $this->assertInstanceOf(Slug::class, $slug);
    }

    #[DataProvider("slugs_invalidos")]
    public function test_rejeita_slug_invalido(string $slug): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Slug($slug);
    }

    #[DataProvider("random_slugs_invalidos")]
    public function test_rejeita_random_slug_invalido(int $slugLength): void
    {
        $this->expectException(InvalidArgumentException::class);

        Slug::random($slugLength);
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
