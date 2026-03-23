<?php

namespace backend\components;

use common\enums\SetOfferImportStatusEnum;
use common\models\SetOfferImport;

final class AdminNavigation
{
    public static function buildOfferImportLinksItem(string $baseLabel, bool $isActive): array
    {
        $failedImportCount = (int)SetOfferImport::find()
            ->where(['status' => SetOfferImportStatusEnum::FAILED->value])
            ->count();

        $url = ['/admin/set-offer-import/index'];
        if ($failedImportCount > 0) {
            $url = [
                '/admin/set-offer-import/index',
                'SetOfferImportSearch[status]' => SetOfferImportStatusEnum::FAILED->value,
            ];
        }

        $label = $baseLabel;
        if ($failedImportCount > 0) {
            $label .= ' <span class="badge rounded-pill text-bg-danger ms-1">' . $failedImportCount . '</span>';
        }

        return [
            'label'       => $label,
            'linkOptions' => ['class' => 'nav-link text-white'],
            'url'         => $url,
            'active'      => $isActive,
        ];
    }
}
