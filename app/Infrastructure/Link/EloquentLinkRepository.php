<?php

namespace App\Infrastructure\Link;

use App\Domain\Link\LinkRepository;
use App\Domain\Link\slug;
use App\Model\Link;

class EloquentLinkRepository implements LinkRepository
{
    public function findBySlug(Slug $slug): ?Link
    {
        return Link::whereSlug((string) $slug)->first();
    }

    public function save(Link $link): void
    {
        $link->save();
    }

    public function existsBySlug(Slug $slug): bool
    {
        return Link::whereSlug((string) $slug)->exists();
    }
}