<?php

namespace frontend\modules\user\models;

use common\models\User;
use common\models\UserSettings;
use frontend\components\T;
use yii\base\Model;

class SettingsForm extends Model
{
    public bool $hide_owned_sets = false;

    private User $user;

    public function __construct(User $user, array $config = [])
    {
        $this->user = $user;
        $this->hide_owned_sets = (bool)($user->settings->hide_owned_sets ?? false);
        parent::__construct($config);
    }

    public function rules(): array
    {
        return [
            [['hide_owned_sets'], 'boolean'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'hide_owned_sets' => T::tr('Hide sets I already own'),
        ];
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $settings = $this->user->getOrCreateSettings();
        $settings->hide_owned_sets = $this->hide_owned_sets ? 1 : 0;

        return $settings->save();
    }
}
