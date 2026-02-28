<?php

namespace common\schema\factory;

use common\models\Set;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;

final class ItemListSchemaFactory
{
    public static function fromDataProvider(ActiveDataProvider $dataProvider): array
    {
        $models = $dataProvider->getModels();
        $pagination = $dataProvider->getPagination();
        $page = $pagination !== false ? (int)$pagination->getPage() : 0;
        $pageSize = $pagination !== false ? (int)$pagination->getPageSize() : count($models);
        $startPosition = ($page * $pageSize) + 1;

        $items = [];
        $position = $startPosition;
        foreach ($models as $model) {
            if (!$model instanceof Set) {
                continue;
            }

            $name = trim((string)$model->name);
            if ($name === '') {
                $name = 'LEGO Set';
            }

            $items[] = [
                '@type'    => 'ListItem',
                'position' => $position,
                'url'      => Url::to("/lego/{$model->slug}", true),
                'name'     => $name,
            ];
            $position++;
        }

        return [
            '@type'           => 'ItemList',
            '@id'             => '#set-list',
            'name'            => 'LEGO Sets',
            'url'             => Url::current([], true),
            'itemListOrder'   => 'https://schema.org/ItemListOrderAscending',
            'numberOfItems'   => (int)$dataProvider->getTotalCount(),
            'itemListElement' => $items,
        ];
    }
}
