<?php

namespace common\enums;

interface BaseEnumInterface extends \BackedEnum
{
    public function label(): string;

    public static function options(): array;
}
