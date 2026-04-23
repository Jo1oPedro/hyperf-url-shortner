<?php

declare(strict_types=1);
namespace App\Domain\Link;

final class RandomSlugGenerator implements SlugGenerator
{
    public function generate(int $length = 7): Slug
    {
        return Slug::random($length);
    }
}