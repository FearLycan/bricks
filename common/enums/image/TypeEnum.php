<?php

namespace common\enums\image;

use common\enums\BaseEnum;

enum TypeEnum: string implements BaseEnum
{
    case SCREENSHOT = 'screenshot';
    case ICON = 'icon';
    case BACKGROUND = 'background';
    case HEADER = 'header';

    public function label(): string
    {
        return match ($this) {
            self::SCREENSHOT => 'Screenshot',
            self::ICON => 'Icon',
            self::BACKGROUND => 'Background',
            self::HEADER => 'Header',
        };
    }
}