<?php

namespace frontend\modules\wizard\controllers;

use common\models\SearchWizardHash;
use frontend\components\T;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;

class DefaultController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow'   => true,
                        'actions' => ['save', 'load'],
                        'roles'   => ['?', '@'],
                    ],
                ],
            ],
            'verbs' => [
                'class'   => VerbFilter::class,
                'actions' => [
                    'save' => ['post'],
                    'load' => ['get'],
                ],
            ],
        ];
    }

    public function actionSave(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $body = Yii::$app->request->getRawBody();
        $data = json_decode($body, true);

        if (!is_array($data) || empty($data['answers'])) {
            throw new BadRequestHttpException(T::tr('Invalid request body'));
        }

        $answers = $data['answers'];

        if (empty($answers['recipient']) || empty($answers['profile']) || empty($answers['budget'])) {
            return ['error' => T::tr('Incomplete answers')];
        }

        $userId = Yii::$app->user->isGuest ? null : Yii::$app->user->id;
        $record = SearchWizardHash::createFromAnswers($answers, $userId);

        return ['hash' => $record->hash];
    }

    public function actionLoad(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $hash   = (string)Yii::$app->request->get('hash', '');
        $record = SearchWizardHash::findByHash($hash);

        if (!$record) {
            Yii::$app->response->statusCode = 404;
            return ['error' => T::tr('Not found')];
        }

        return $record->getPublicData();
    }
}
