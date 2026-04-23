<?php

declare(strict_types=1);
namespace App\Domain\Link;

enum LinkStatus: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case DISABLED = 'disabled';
}
