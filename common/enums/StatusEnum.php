<?php

namespace common\enums;

enum StatusEnum: int implements BaseEnumInterface
{
    use EnumOptionsTrait;

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
