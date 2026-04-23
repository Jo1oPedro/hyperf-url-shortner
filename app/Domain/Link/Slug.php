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
        return $this->slug;
    }

    public static function random(int $length = 7): self
    {
        if($length < 4 || $length > 16) {
            throw new \InvalidArgumentException("Slug length must be between 4 and 16");
        }

        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-';
        $maxIndex = strlen($characters) - 1;
        $slug = "";

        for ($i = 0; $i < $length; $i++) {
            $slug .= $characters[random_int(0, $maxIndex)];
        }

        return new self($slug);
    }
}