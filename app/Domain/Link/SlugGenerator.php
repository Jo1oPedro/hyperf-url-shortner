<?php

namespace App\Domain\Link;

interface SlugGenerator
{
    public function generate(int $length = 7): Slug;
}