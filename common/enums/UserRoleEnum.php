<?php

namespace common\enums;

enum UserRoleEnum: string implements BaseEnumInterface
{
    use EnumOptionsTrait;

    case USER = 'user';
    case ADMIN = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::USER => 'User',
            self::ADMIN => 'Admin',
        };
    }
}
