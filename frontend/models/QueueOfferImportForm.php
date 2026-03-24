<?php

namespace frontend\models;

use common\enums\SetOfferImportStatusEnum;
use common\models\Set;
use common\models\SetOfferImport;
use yii\base\Model;

final class QueueOfferImportForm extends Model
{
    public int $setId = 0;
    public string $offerUrl = '';

    public function rules(): array
    {
        return [
            [['setId', 'offerUrl'], 'required'],
            [['setId'], 'integer', 'min' => 1],
            [['setId'], 'exist', 'targetClass' => Set::class, 'targetAttribute' => ['setId' => 'id']],
            [['offerUrl'], 'filter', 'filter' => 'trim'],
            [['offerUrl'], 'string', 'max' => 2000],
            [['offerUrl'], 'url', 'defaultScheme' => 'https'],
            [['offerUrl'], function (): void {
                $host = strtolower((string)parse_url($this->offerUrl, PHP_URL_HOST));
                if ($host === '' || !str_contains($host, 'aliexpress.com')) {
                    $this->addError('offerUrl', 'Only AliExpress links are allowed.');
                }
            }],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'setId' => 'Set ID',
            'offerUrl' => 'AliExpress link',
        ];
    }

    public function saveToQueue(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $alreadyPending = SetOfferImport::find()
            ->where([
                'set_id' => $this->setId,
                'input_url' => $this->offerUrl,
                'status' => SetOfferImportStatusEnum::PENDING->value,
            ])
            ->exists();

        if ($alreadyPending) {
            $this->addError('offerUrl', 'This link is already in queue.');

            return false;
        }

        $importTask = new SetOfferImport();
        $importTask->set_id = $this->setId;
        $importTask->input_url = $this->offerUrl;
        $importTask->status = SetOfferImportStatusEnum::PENDING->value;

        if (!$importTask->save()) {
            foreach ($importTask->getErrors() as $attribute => $messages) {
                foreach ($messages as $message) {
                    $this->addError($attribute, $message);
                }
            }

            if (!$this->hasErrors()) {
                $this->addError('offerUrl', 'Could not save import link.');
            }

            return false;
        }

        return true;
    }
}
