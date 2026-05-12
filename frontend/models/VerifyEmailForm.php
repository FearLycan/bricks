<?php

namespace frontend\models;

use common\models\User;
use yii\base\InvalidArgumentException;
use yii\base\Model;

class VerifyEmailForm extends Model
{
    private User $_user;

    public function __construct(string $token, array $config = [])
    {
        if (empty($token)) {
            throw new InvalidArgumentException('Email verification token cannot be blank.');
        }

        $user = User::findByVerificationToken($token);
        if (!$user) {
            throw new InvalidArgumentException('Wrong email verification token.');
        }

        $this->_user = $user;

        parent::__construct($config);
    }

    public function verifyEmail(): ?User
    {
        $user = $this->_user;
        $user->status = User::STATUS_ACTIVE;

        return $user->save(false) ? $user : null;
    }
}
