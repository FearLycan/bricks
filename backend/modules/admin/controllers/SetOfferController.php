<?php

namespace backend\modules\admin\controllers;

use common\models\SetOffer;
use common\models\Store;
use yii\filters\VerbFilter;
use backend\components\Controller;
use yii\web\NotFoundHttpException;

class SetOfferController extends Controller
{
    public function behaviors(): array
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class'   => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->currency_code = strtoupper(trim((string)$model->currency_code));
            if ($model->currency_code === '') {
                $model->currency_code = 'USD';
            }
            if ($model->save()) {
                return $this->redirect(['/admin/set/view', 'id' => $model->set_id, '#' => 'offers']);
            }
        }

        return $this->render('update', [
            'model'      => $model,
            'storesList' => Store::getAvailableStoresList(),
        ]);
    }

    public function actionCreate($setId)
    {
        $model = new SetOffer();
        $model->set_id = (int)$setId;
        $model->currency_code = 'USD';
        $model->is_manual_override = 1;
        $model->source = 'admin';

        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->set_id = (int)$setId;
            $model->is_manual_override = 1;
            $model->currency_code = strtoupper(trim((string)$model->currency_code));
            if ($model->currency_code === '') {
                $model->currency_code = 'USD';
            }
            if ($model->save()) {
                return $this->redirect(['/admin/set/view', 'id' => $model->set_id, '#' => 'offers']);
            }
        }

        return $this->render('create', [
            'model'      => $model,
            'storesList' => Store::getAvailableStoresList(),
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $setId = $model->set_id;
        $model->delete();

        return $this->redirect(['/admin/set/view', 'id' => $setId, '#' => 'offers']);
    }

    protected function findModel($id): SetOffer
    {
        if (($model = SetOffer::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
