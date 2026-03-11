<?php

namespace common\enums\image;

use common\enums\BaseEnumInterface;
use common\enums\EnumOptionsTrait;

enum KindEnum: string implements BaseEnumInterface
{
    use EnumOptionsTrait;

    case MAIN = 'main';
    case ADDITIONAL = 'additional';

    public function label(): string
    {
        return match ($this) {
            self::MAIN => 'main',
            self::ADDITIONAL => 'additional',
        };
    }
}
