<?php

namespace frontend\models;

use common\models\User;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\Model;

class ResetPasswordForm extends Model
{
    public string $password = '';
    public string $password_repeat = '';

    private User $_user;

    public function __construct(string $token, array $config = [])
    {
        if (empty($token)) {
            throw new InvalidArgumentException('Password reset token cannot be blank.');
        }

        $user = User::findByPasswordResetToken($token);
        if (!$user) {
            throw new InvalidArgumentException('Wrong password reset token.');
        }

        $this->_user = $user;

        parent::__construct($config);
    }

    public function rules(): array
    {
        return [
            ['password', 'required'],
            ['password', 'string', 'min' => Yii::$app->params['user.passwordMinLength']],
            ['password', 'string', 'max' => 72],
            ['password', 'validatePasswordStrength'],

            ['password_repeat', 'required'],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message' => 'Passwords do not match.'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'password_repeat' => 'Repeat password',
        ];
    }

    public function validatePasswordStrength(string $attribute): void
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

    public function resetPassword(): bool
    {
        $user = $this->_user;
        $user->setPassword($this->password);
        $user->removePasswordResetToken();
        $user->generateAuthKey();

        return $user->save();
    }
}
