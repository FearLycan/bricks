<?php

namespace backend\modules\admin\models;

use common\models\ThemeGroup;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class ThemeGroupSearch extends ThemeGroup
{
    public function rules(): array
    {
        return [
            [['id', 'status'], 'integer'],
            [['name', 'slug', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = ThemeGroup::find();

        $this->load($params);
        if (!$this->validate()) {
            return $this->buildQuery($query);
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'slug', $this->slug]);

        return $this->buildQuery($query);
    }

    private function buildQuery($query): ActiveDataProvider
    {
        return new ActiveDataProvider([
            'query'      => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort'       => [
                'defaultOrder' => ['id' => SORT_DESC],
            ],
        ]);
    }
}
