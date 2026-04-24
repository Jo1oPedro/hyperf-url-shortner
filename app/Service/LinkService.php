<?php

namespace App\Service;

use App\Domain\Link\LinkRepository;
use App\Domain\Link\Slug;
use App\Domain\Link\SlugGenerator;
use App\Exception\SlugAlreadyExistsException;
use App\Model\Link;

class LinkService
{
    public function __construct(
        private readonly LinkRepository $linkRepository,
        private readonly SlugGenerator $randomSlugGenerator,
    ) {}

    public function create(string $url, ?string $customSlug = null): Link
    {
        if(!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("Invalid URL: {$url}");
        }

        $slug = $customSlug !== null
            ? new Slug($customSlug)
            : $this->randomSlugGenerator->generate();

        if($this->linkRepository->existsBySlug($slug)) {
            throw new SlugAlreadyExistsException("Slug '{$slug}' already exists");
        }

        $link = new Link();
        $link->original_url = $url;
        $link->slug = (string) $slug;

        $this->linkRepository->save($link);

        return $link;
    }
}