<?php

namespace frontend\models\searches;

use common\models\Set;
use yii\base\Model;
use yii\data\ActiveDataProvider;

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
            [['number', 'name', 'slug', 'brickset_url', 'created_at', 'updated_at'], 'safe'],
            [['rating'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
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
        $query->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC,]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => 'created_at DESC, id DESC'],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

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
            ->andFilterWhere(['like', 'brickset_url', $this->brickset_url]);

        return $dataProvider;
    }
}
