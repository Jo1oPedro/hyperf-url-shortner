<?php

namespace App\Domain\Link;

readonly class Slug
{
    private string $slug;
    public function __construct($slug)
    {
        if(!preg_match("/^[a-zA-Z0-9_-]{4,16}+$/", $slug, $matches)) {
            throw new \InvalidArgumentException("Slug '{$slug}' is invalid");
        }

        $this->slug = $slug;
    }

    public function __toString(): string
    {

    }
}