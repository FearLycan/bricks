<?php

namespace common\enums\image;

use common\enums\BaseEnum;

enum StatusEnum: int implements BaseEnum
{
    case ACTIVE = 1;
    case INACTIVE = 0;

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
        };
    }
}
