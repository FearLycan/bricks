<?php

namespace backend\modules\admin\models\forms;

use yii\base\Model;

final class AliExpressOfferImportForm extends Model
{
    public string $offerUrl = '';

    public function rules(): array
    {
        return [
            [['offerUrl'], 'filter', 'filter' => 'trim'],
            [['offerUrl'], 'required'],
            [['offerUrl'], 'string', 'max' => 2000],
            [['offerUrl'], 'url', 'defaultScheme' => 'https'],
            [['offerUrl'], function (): void {
                $host = strtolower((string)parse_url($this->offerUrl, PHP_URL_HOST));
                if ($host === '') {
                    return;
                }

                if (!str_contains($host, 'aliexpress.com')) {
                    $this->addError('offerUrl', 'Only AliExpress links are allowed.');
                }
            }],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'offerUrl' => 'AliExpress URL',
        ];
    }
}
