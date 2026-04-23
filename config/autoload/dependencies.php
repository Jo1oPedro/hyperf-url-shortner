<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

use App\Domain\Link\LinkRepository;
use App\Domain\Link\RandomSlugGenerator;
use App\Domain\Link\SlugGenerator;
use App\Infrastructure\Link\EloquentLinkRepository;

return [
    SlugGenerator::class => RandomSlugGenerator::class,
    LinkRepository::class => EloquentLinkRepository::class
];
