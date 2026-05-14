<?php

namespace frontend\modules\user\models;

use common\models\User;
use common\models\UserSettings;
use frontend\components\T;
use Yii;
use yii\base\Model;

class SettingsForm extends Model
{
    public bool $hide_owned_sets = false;
    public ?string $preferred_language = null;

    private User $user;

    public function __construct(User $user, array $config = [])
    {
        $this->user = $user;
        $settings = $user->settings;
        $this->hide_owned_sets = (bool)($settings->hide_owned_sets ?? false);
        $this->preferred_language = $settings->preferred_language ?? (string)Yii::$app->language;
        parent::__construct($config);
    }

    public function rules(): array
    {
        return [
            [['hide_owned_sets'], 'boolean'],
            [['preferred_language'], 'in', 'range' => UserSettings::SUPPORTED_LANGUAGES, 'strict' => true, 'skipOnEmpty' => true],
            [['preferred_language'], 'default', 'value' => null],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'hide_owned_sets'    => T::tr('Hide sets I already own'),
            'preferred_language' => T::tr('Preferred language'),
        ];
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $settings = $this->user->getOrCreateSettings();
        $settings->hide_owned_sets = $this->hide_owned_sets ? 1 : 0;
        $settings->preferred_language = $this->preferred_language ?: null;

        return $settings->save();
    }
}
