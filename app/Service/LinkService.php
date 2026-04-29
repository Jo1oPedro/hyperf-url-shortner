<?php

namespace App\Service;

use App\Domain\Link\LinkRepository;
use App\Domain\Link\LinkStatus;
use App\Domain\Link\Slug;
use App\Domain\Link\SlugGenerator;
use App\DTO\CreateLinkDTO;
use App\DTO\LinkDTO;
use App\Exception\LinkGoneException;
use App\Exception\LinkNotFoundException;
use App\Exception\SlugAlreadyExistsException;
use App\Job\IncrementClicksJob;
use App\Model\Link;
use Carbon\Carbon;

class LinkService
{
    public function __construct(
        private readonly LinkRepository $linkRepository,
        private readonly SlugGenerator $randomSlugGenerator,
        private readonly LinkCache $linkCache,
        private readonly QueueService $queueService,
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
        $link->owner_user_id = $createLinkDTO->ownerUserId;

        $link = $this->linkRepository->save($link);
        $this->linkCache->invalidate($link->slug);

        return LinkDTO::fromModel($link);
    }

    public function resolve(string $slug): LinkDTO
    {
        $slugVO = new Slug($slug);

        if($this->linkCache->isKnownMissing($slug)) {
            throw new LinkNotFoundException("Link '{$slug}' not found");
        }

        $cached = $this->linkCache->get($slug);

        if($cached === null) {
            $link = $this->linkRepository->findBySlug($slugVO);

            if($link === null) {
                $this->linkCache->markMissing($slug);
                throw new LinkNotFoundException("Link '{$slug}' not found");
            }

            $cached = [
                "original_url" => $link->original_url,
                "status" => $link->status,
                "expires_at" => $link->expires_at,
            ];

            if(
                $cached["status"] === LinkStatus::ACTIVE->value
                && ($cached["expires_at"] === null || strtotime($cached["expires_at"]) > time())
            ) {
                $this->linkCache->set($slug, $cached);
            }
        }

        if($cached["status"] !== LinkStatus::ACTIVE->value) {
            throw new LinkGoneException("Link '{$slug}' is not active");
        }

        if($cached["expires_at"] !== null && strtotime($cached["expires_at"]) < time()) {
            throw new LinkGoneException("Link '{$slug}' expired");
        }

        $this->queueService->push(new IncrementClicksJob($slug));

        return new LinkDTO(
            slug: $slug,
            originalUrl: $cached["original_url"],
            status: $cached["status"],
            expiresAt: $cached["expires_at"] !== null ? Carbon::parse($cached["expires_at"]) : null,
        );
    }

    public function linkStats(string $slug): LinkDTO
    {
        $slugVO = new Slug($slug);
        $link = $this->linkRepository->findBySlug($slugVO);
        if($link === null) {
            throw new LinkNotFoundException("Link '{$slug}' not found");
        }

        return LinkDTO::fromModel($link);
    }
}