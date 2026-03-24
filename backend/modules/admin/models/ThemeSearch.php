<?php

namespace backend\modules\admin\models;

use common\models\Theme;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class ThemeSearch extends Theme
{
    public function rules(): array
    {
        return [
            [['id', 'parent_id', 'group_id', 'sets_count', 'year_from', 'year_to', 'status'], 'integer'],
            [['name', 'slug', 'description', 'img', 'custom_css', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = Theme::find()->with(['group', 'parent']);

        $this->load($params);
        if (!$this->validate()) {
            return $this->buildQuery($query);
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'group_id' => $this->group_id,
            'sets_count' => $this->sets_count,
            'year_from' => $this->year_from,
            'year_to' => $this->year_to,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'slug', $this->slug])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'img', $this->img])
            ->andFilterWhere(['like', 'custom_css', $this->custom_css]);

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
