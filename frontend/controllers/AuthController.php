<?php

namespace frontend\controllers;

use common\helpers\RateLimiter;
use common\models\LoginForm;
use common\models\User;
use frontend\components\SeoHelper;
use frontend\components\T;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResendVerificationEmailForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\VerifyEmailForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;
use yii\web\TooManyRequestsHttpException;

class AuthController extends Controller
{
    /** Rate-limit windows (max attempts, seconds). */
    private const LOGIN_LIMIT          = [5, 900];
    private const SIGNUP_LIMIT         = [10, 3600];
    private const PASSWORD_RESET_LIMIT = [5, 900];
    private const RESEND_VERIFY_LIMIT  = [5, 900];
    private const PER_EMAIL_LIMIT      = [1, 300];

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only'  => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                    [
                        'actions' => ['signup'],
                        'allow'   => true,
                        'roles'   => ['?'],
                    ],
                ],
            ],
            'verbs'  => [
                'class'   => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();

        if (Yii::$app->request->isPost) {
            $this->throttle('login:' . Yii::$app->request->userIP, self::LOGIN_LIMIT);
        }

        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            RateLimiter::reset('login:' . Yii::$app->request->userIP);
            return $this->applyPreferredLanguageRedirect()
                ?? $this->goBack();
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * After a successful login, if the user has a saved language preference
     * that differs from the URL-derived language, redirect the returnUrl into
     * that language. Returns null if no redirect is needed.
     */
    private function applyPreferredLanguageRedirect(): ?Response
    {
        $identity = Yii::$app->user->identity;
        if (!$identity instanceof User) {
            return null;
        }

        $preferred = $identity->settings->preferred_language ?? null;
        if ($preferred === null || $preferred === Yii::$app->language) {
            return null;
        }

        Yii::$app->language = $preferred;
        $returnUrl = Yii::$app->user->getReturnUrl();

        return $this->redirect(SeoHelper::rewriteUrlLanguage((string)$returnUrl, $preferred));
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionSignup()
    {
        $model = new SignupForm();

        if (Yii::$app->request->isPost) {
            $this->throttle('signup:' . Yii::$app->request->userIP, self::SIGNUP_LIMIT);
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->signup()) {
            $hours = (int)(Yii::$app->params['user.verificationTokenExpire'] / 3600);
            Yii::$app->session->setFlash('success', T::tr(
                'Thank you for registration. Please check your inbox for a verification email — the link is valid for {hours} hours.',
                ['hours' => $hours]
            ));
            return $this->goHome();
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    public function actionRequestPasswordReset()
    {
        if (($redirect = $this->guestOnly()) !== null) {
            return $redirect;
        }

        $model = new PasswordResetRequestForm();

        if (Yii::$app->request->isPost) {
            $this->throttle('pwreset:ip:' . Yii::$app->request->userIP, self::PASSWORD_RESET_LIMIT);
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $this->throttle('pwreset:email:' . strtolower($model->email), self::PER_EMAIL_LIMIT);
            $model->sendEmail();
            Yii::$app->session->setFlash('success', T::tr('If an account with this email exists, you will receive a reset link shortly.'));

            return $this->goHome();
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    public function actionResetPassword($token)
    {
        if (($redirect = $this->guestOnly()) !== null) {
            return $redirect;
        }

        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', T::tr('New password saved. You can now sign in.'));

            return $this->redirect(['auth/login']);
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    public function actionVerifyEmail($token)
    {
        if (($redirect = $this->guestOnly()) !== null) {
            return $redirect;
        }

        try {
            $model = new VerifyEmailForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if (($user = $model->verifyEmail()) && Yii::$app->user->login($user)) {
            Yii::$app->session->setFlash('success', T::tr('Your email has been confirmed!'));
            return $this->goHome();
        }

        Yii::$app->session->setFlash('error', T::tr('Sorry, we are unable to verify your account with provided token.'));
        return $this->goHome();
    }

    public function actionResendVerificationEmail()
    {
        if (($redirect = $this->guestOnly()) !== null) {
            return $redirect;
        }

        $model = new ResendVerificationEmailForm();

        if (Yii::$app->request->isPost) {
            $this->throttle('resend:ip:' . Yii::$app->request->userIP, self::RESEND_VERIFY_LIMIT);
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $this->throttle('resend:email:' . strtolower($model->email), self::PER_EMAIL_LIMIT);
            $model->sendEmail();
            Yii::$app->session->setFlash('success', T::tr('If an account with this email is awaiting verification, you will receive a new link shortly.'));

            return $this->goHome();
        }

        return $this->render('resendVerificationEmail', [
            'model' => $model,
        ]);
    }

    /**
     * Redirects logged-in users away from guest-only auth flows
     * (verify-email, reset-password, etc.) to prevent identity swaps and confusion.
     */
    private function guestOnly(): ?Response
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        return null;
    }

    /**
     * @param array{0:int,1:int} $limit
     */
    private function throttle(string $key, array $limit): void
    {
        [$maxAttempts, $window] = $limit;
        if (!RateLimiter::hit($key, $maxAttempts, $window)) {
            throw new TooManyRequestsHttpException(T::tr('Too many attempts. Please try again later.'));
        }
    }
}
