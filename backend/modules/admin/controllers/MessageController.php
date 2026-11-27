<?php

namespace backend\modules\admin\controllers;

use backend\components\Controller;
use common\models\ContactMessage;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/** Inbox for the contact form (frontend /site/contact). */
class MessageController extends Controller
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['delete' => ['POST'], 'unread' => ['POST']]],
        ]);
    }

    public function actionIndex(): string
    {
        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query'      => ContactMessage::find(),
                'sort'       => ['defaultOrder' => ['id' => SORT_DESC]],
                'pagination' => ['pageSize' => 30],
            ]),
        ]);
    }

    public function actionView(int $id): string
    {
        $m = $this->findMessage($id);
        if ($m->read_at === null) {
            $m->read_at = time();
            $m->save(false, ['read_at']);
        }

        return $this->render('view', ['m' => $m]);
    }

    public function actionUnread(int $id): Response
    {
        $m = $this->findMessage($id);
        $m->read_at = null;
        $m->save(false, ['read_at']);

        return $this->redirect(['index']);
    }

    public function actionDelete(int $id): Response
    {
        $this->findMessage($id)->delete();
        Yii::$app->session->setFlash('success', 'Message deleted.');

        return $this->redirect(['index']);
    }

    private function findMessage(int $id): ContactMessage
    {
        return ContactMessage::findOne($id) ?? throw new NotFoundHttpException();
    }
}
