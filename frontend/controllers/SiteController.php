<?php

namespace frontend\controllers;

use frontend\components\LegoGlossary;
use frontend\components\LegoInterests;
use frontend\components\T;
use frontend\models\ContactForm;
use Yii;
use yii\web\Controller;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error'   => [
                'class' => \yii\web\ErrorAction::class,
            ],
            'captcha' => [
                'class'           => \yii\captcha\CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', T::tr('Thanks for reaching out — we’ll get back to you soon.'));
            } else {
                Yii::$app->session->setFlash('error', T::tr('There was an error sending your message.'));
            }

            return $this->refresh();
        }

        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Displays the public LEGO glossary at /glossary.
     */
    public function actionGlossary(): string
    {
        return $this->render('glossary', [
            'categories' => LegoGlossary::getCategories(),
        ]);
    }

    /**
     * Displays the curated "Interests" landing page at /interests.
     */
    public function actionInterests(): string
    {
        return $this->render('interests', [
            'tiles' => LegoInterests::getTiles(),
        ]);
    }
}
