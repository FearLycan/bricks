<?php

namespace backend\modules\admin\models;

use common\models\Store;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class StoreSearch extends Store
{
    public function rules(): array
    {
        return [
            [['id', 'status'], 'integer'],
            [['code', 'name', 'url', 'logo', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = Store::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'url', $this->url])
            ->andFilterWhere(['like', 'logo', $this->logo]);

        return $dataProvider;
    }
}
