<?php

namespace common\enums;

enum UserStatusEnum: int implements BaseEnumInterface
{
    use EnumOptionsTrait;

    case DELETED = 0;
    case INACTIVE = 9;
    case ACTIVE = 10;

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::DELETED => 'Deleted',
        };
    }
}
