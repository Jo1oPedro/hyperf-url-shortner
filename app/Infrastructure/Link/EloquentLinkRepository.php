<?php

namespace App\Infrastructure\Link;

use App\Domain\Link\LinkRepository;
use App\Domain\Link\slug;
use App\Model\Link;
use App\Observability\Tracer;

class EloquentLinkRepository implements LinkRepository
{
    public function __construct(
        private Tracer $tracer
    ) {}

    public function findBySlug(Slug $slug): ?Link
    {
        return $this->tracer->trace(
            "db.links.findBySlug",
            function () use ($slug) {
                return Link::whereSlug((string) $slug)->first();
            },
            [
                "db.system" => "mysql",
                "db.operation" => "SELECT",
                "db.table" => "links"
            ]
        );
    }

    public function save(Link $link): Link
    {
        return $this->tracer->trace(
            "db.links.save",
            function () use ($link) {
                $link->save();
                return $link->fresh();
            },
            [
                "db.system" => "mysql",
                "db.operation" => "INSERT_OR_UPDATE",
                "db.table" => "links"
            ]
        );
    }

    public function existsBySlug(Slug $slug): bool
    {
        return $this->tracer->trace(
            "db.links.existsBySlug",
            function () use ($slug) {
                return Link::whereSlug((string) $slug)->exists();
            },
            [
                "db.system" => "mysql",
                "db.operation" => "SELECT_EXISTS",
                "db.table" => "links"
            ]
        );
    }
}