<?php

namespace frontend\models\searches;

use common\models\Set;
use common\models\Theme;
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

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => 'created_at DESC, id DESC'],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'theme_id'    => $this->theme_id,
            'year'        => $this->year,
            'subtheme_id' => $this->subtheme_id, //to show subthemes views
        ]);

        if ($this->name) {
            if (is_numeric($this->name)) {
                $query->andFilterWhere(['=', 'number', $this->name]);
            } else {
                $themeIdsQuery = Theme::find()
                    ->select('id')
                    ->where(['like', 'name', $this->name]);

                $query->andWhere([
                    'or',
                    ['like', Set::tableName() . '.name', $this->name],
                    ['theme_id' => $themeIdsQuery],
                    ['subtheme_id' => $themeIdsQuery],
                ]);
            }
        }

        return $dataProvider;
    }

    public function formName(): string
    {
        return '';
    }
}
