<?php

namespace App\Enums\Common;

use App\Traits\EnumTrait;

enum Status: int
{
    use EnumTrait;

    case ACTIVE = 1;
    case INACTIVE = 0;
}
