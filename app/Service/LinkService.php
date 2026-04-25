<?php

namespace App\Service;

use App\Domain\Link\LinkRepository;
use App\Domain\Link\Slug;
use App\Domain\Link\SlugGenerator;
use App\DTO\CreateLinkDTO;
use App\DTO\LinkDTO;
use App\Exception\SlugAlreadyExistsException;
use App\Model\Link;

class LinkService
{
    public function __construct(
        private readonly LinkRepository $linkRepository,
        private readonly SlugGenerator $randomSlugGenerator,
    ) {}

    public function create(CreateLinkDTO $createLinkDTO): LinkDTO
    {
        if(!filter_var($createLinkDTO->url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("Invalid URL: {$createLinkDTO->url}");
        }

        $slug = $createLinkDTO->slug !== null
            ? new Slug($createLinkDTO->slug)
            : $this->randomSlugGenerator->generate();

        if($this->linkRepository->existsBySlug($slug)) {
            throw new SlugAlreadyExistsException("Slug '{$slug}' already exists");
        }

        $link = new Link();
        $link->original_url = $createLinkDTO->url;
        $link->slug = (string) $slug;
        $link->expires_at = $createLinkDTO->expiresAt;

        $link = $this->linkRepository->save($link);

        return LinkDTO::fromModel($link);
    }
}