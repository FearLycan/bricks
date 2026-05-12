<?php

namespace frontend\models;

use common\models\User;
use Yii;
use yii\base\Model;

class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;

    public function rules()
    {
        return [
            ['username', 'trim'],
            ['username', 'required'],
            ['username', 'string', 'min' => 3, 'max' => 50],
            ['username', 'match', 'pattern' => '/^[a-zA-Z0-9_\-\.]+$/', 'message' => 'Username may only contain letters, numbers, underscores, hyphens and dots.'],
            ['username', 'match', 'pattern' => '/admin/i', 'not' => true, 'message' => 'This username is not allowed.'],
            ['username', 'unique', 'targetClass' => User::class, 'message' => 'This username has already been taken.'],

            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 100],
            ['email', 'unique', 'targetClass' => User::class, 'message' => 'This email address has already been taken.'],

            ['password', 'required'],
            ['password', 'string', 'min' => Yii::$app->params['user.passwordMinLength']],
            ['password', 'string', 'max' => 60],
            ['password', 'validatePasswordStrength'],
        ];
    }

    public function validatePasswordStrength($attribute)
    {
        if ($this->hasErrors($attribute)) {
            return;
        }

        $password = $this->$attribute;
        $missing = [];

        if (!preg_match('/[A-Z]/', $password)) {
            $missing[] = 'an uppercase letter';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $missing[] = 'a lowercase letter';
        }
        if (!preg_match('/\d/', $password)) {
            $missing[] = 'a number';
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $missing[] = 'a special character';
        }

        if ($missing) {
            $this->addError($attribute, 'Password must contain ' . implode(', ', $missing) . '.');
        }
    }

    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->username = $this->username;
        $user->email = $this->email;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->generateEmailVerificationToken();
        $user->status = User::STATUS_INACTIVE;

        if (!$user->save()) {
            return null;
        }

        $this->sendEmail($user);

        return $user;
    }

    private function sendEmail(User $user): bool
    {
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
