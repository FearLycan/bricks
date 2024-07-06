<?php

namespace common\enums\image;

use common\enums\BaseEnum;

enum TypeEnum: string implements BaseEnum
{
    case SCREENSHOT = 'screenshot';
    case ICON = 'icon';
    case BACKGROUND = 'background';
    case HEADER = 'header';
    case THUMBNAIL = 'thumbnail';
    case IMAGE = 'image';

    public function label(): string
    {
        return match ($this) {
            self::SCREENSHOT => 'Screenshot',
            self::ICON => 'Icon',
            self::BACKGROUND => 'Background',
            self::HEADER => 'Header',
            self::THUMBNAIL => 'Thumbnail',
            self::IMAGE => 'Image'
        };
    }
}