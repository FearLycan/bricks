<?php

namespace frontend\models;

use common\models\User;
use Yii;
use yii\base\Model;

class ResendVerificationEmailForm extends Model
{
    public $email;

    public function rules()
    {
        return [
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
        ];
    }

    public function sendEmail(): bool
    {
        $user = User::findOne(['status' => User::STATUS_INACTIVE, 'email' => $this->email]);
        if (!$user) {
            return true;
        }

        return Yii::$app->mailer
            ->compose(
                ['html' => 'emailVerify-html', 'text' => 'emailVerify-text'],
                ['user' => $user]
            )
            ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
            ->setTo($this->email)
            ->setSubject('Account registration at ' . Yii::$app->name)
            ->send();
    }
}
