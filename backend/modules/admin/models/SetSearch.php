<?php

namespace backend\modules\admin\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Set;

/**
 * SetSearch represents the model behind the search form of `common\models\Set`.
 */
class SetSearch extends Set
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'theme_id', 'status', 'number_variant', 'minifigures', 'year', 'pieces', 'released', 'price', 'age', 'subtheme_id'], 'integer'],
            [['number', 'name', 'slug', 'brickset_url', 'dimensions', 'availability', 'created_at', 'updated_at', 'description'], 'safe'],
            [['rating'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search(array $params): ActiveDataProvider
    {
        $query = Set::find();

        // add conditions that should always apply here

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $this->buildQuery($query);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id'             => $this->id,
            'theme_id'       => $this->theme_id,
            'status'         => $this->status,
            'number_variant' => $this->number_variant,
            'minifigures'    => $this->minifigures,
            'year'           => $this->year,
            'pieces'         => $this->pieces,
            'released'       => $this->released,
            'rating'         => $this->rating,
            'price'          => $this->price,
            'age'            => $this->age,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
            'subtheme_id'    => $this->subtheme_id,
        ]);

        $query->andFilterWhere(['like', 'number', $this->number])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'slug', $this->slug])
            ->andFilterWhere(['like', 'brickset_url', $this->brickset_url])
            ->andFilterWhere(['like', 'dimensions', $this->dimensions])
            ->andFilterWhere(['like', 'availability', $this->availability])
            ->andFilterWhere(['like', 'description', $this->description]);

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
