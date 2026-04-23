<?php

namespace App\Domain\Link;

enum LinkStatus: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case DISABLED = 'disabled';
}
