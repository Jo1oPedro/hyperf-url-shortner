<?php

namespace App\Domain\Link;

use App\Model\Link;

interface LinkRepository
{
    public function findBySlug(Slug $slug): ?Link;

    public function save(Link $link): void;
    public function existsBySlug(Slug $slug): bool;
}