<?php

namespace App\Domain\Link;

class RandomSlugGenerator implements SlugGenerator
{
    public function generate(int $length = 7): Slug
    {
        return Slug::random($length);
    }
}