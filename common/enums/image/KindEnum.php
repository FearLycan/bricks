<?php

namespace common\enums\image;

use common\enums\BaseEnum;

enum KindEnum: string implements BaseEnum
{
    case MAIN = 'mian';
    case ADDITIONAL = 'additional';

    public function label(): string
    {
        return match ($this) {
            self::MAIN => 'main',
            self::ADDITIONAL => 'additional',
        };
    }
}
