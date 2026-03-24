<?php

namespace common\enums;

enum SetOfferImportStatusEnum: string implements BaseEnumInterface
{
    use EnumOptionsTrait;

    case AWAITING_REVIEW = 'awaiting_review';
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case DONE = 'done';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::AWAITING_REVIEW => 'Awaiting Review',
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::DONE => 'Done',
            self::FAILED => 'Failed',
        };
    }
}
