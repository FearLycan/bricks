<?php

namespace frontend\models\searches;

use common\enums\image\StatusEnum;
use common\models\Set;
use common\models\Theme;
use frontend\components\T;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * SetSearch represents the model behind the search form of `common\models\Set`.
 */
class SetSearch extends Set
{
    public ?string $sort_option = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'theme_id', 'status', 'number_variant', 'minifigures', 'year', 'pieces', 'released', 'price', 'age', 'subtheme_id'], 'integer'],
            [['number', 'name', 'slug', 'brickset_url', 'created_at', 'updated_at', 'sort_option'], 'safe'],
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
        $query = Set::find()->andFilterCompare('status', StatusEnum::ACTIVE->value);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            //'sort'  => ['defaultOrder' => ['year' => SORT_DESC, 'id' => SORT_ASC]], //moved to getSortOptions()
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        if ($this->subtheme_id) {
            $query->andWhere(['subtheme_id' => $this->subtheme_id]);
        }

        if ($this->theme && $this->subtheme_id) {
            $query->andWhere(['theme_id' => $this->theme_id]);
        }

        if ($this->theme_id && !$this->subtheme_id) {
            $allSubthemeIds = Theme::find()->select('id')->where(['name' => $this->theme->name])->column();
            $query->andWhere([
                'or',
                ['theme_id' => $allSubthemeIds],
                ['subtheme_id' => $allSubthemeIds],
            ]);
        }

        $query->andFilterWhere([
            'year' => $this->year,
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

        $this->applySortOption($query);

        return $dataProvider;
    }

    public function formName(): string
    {
        return '';
    }

    public static function getSortOptions(): array
    {
        return [
            'price_asc'            => T::tr('price: low to high'),
            'price_desc'           => T::tr('price: high to low'),
            'pieces_asc'           => T::tr('pieces: low to high'),
            'pieces_desc'          => T::tr('pieces: high to low'),
            'year_desc'            => T::tr('release year: newest'),
            'year_asc'             => T::tr('release year: oldest'),
            'name_asc'             => T::tr('set name: A-Z'),
            'name_desc'            => T::tr('set name: Z-A'),
            'price_per_piece_asc'  => T::tr('price/piece: low to high'),
            'price_per_piece_desc' => T::tr('price/piece: high to low'),
            'minifigures_asc'      => T::tr('minifigures: low to high'),
            'minifigures_desc'     => T::tr('minifigures: high to low'),
        ];
    }

    private function applySortOption($query): void
    {
        switch ($this->sort_option) {
            case 'price_asc':
                $query->orderBy(['price' => SORT_ASC, 'id' => SORT_ASC]);
                break;
            case 'price_desc':
                $query->orderBy(['price' => SORT_DESC, 'id' => SORT_ASC]);
                break;
            case 'pieces_asc':
                $query->orderBy(['pieces' => SORT_ASC, 'id' => SORT_ASC]);
                break;
            case 'pieces_desc':
                $query->orderBy(['pieces' => SORT_DESC, 'id' => SORT_ASC]);
                break;
            case 'year_desc':
                $query->orderBy(['year' => SORT_DESC, 'id' => SORT_ASC]);
                break;
            case 'year_asc':
                $query->orderBy(['year' => SORT_ASC, 'id' => SORT_ASC]);
                break;
            case 'name_asc':
                $query->orderBy(['name' => SORT_ASC, 'id' => SORT_ASC]);
                break;
            case 'name_desc':
                $query->orderBy(['name' => SORT_DESC, 'id' => SORT_ASC]);
                break;
            case 'price_per_piece_asc':
                $query->orderBy(new Expression('CASE WHEN pieces > 0 THEN price / pieces END ASC'))
                    ->addOrderBy(['id' => SORT_ASC]);
                break;
            case 'price_per_piece_desc':
                $query->orderBy(new Expression('CASE WHEN pieces > 0 THEN price / pieces END DESC'))
                    ->addOrderBy(['id' => SORT_ASC]);
                break;
            case 'minifigures_asc':
                $query->orderBy(['minifigures' => SORT_ASC, 'id' => SORT_ASC]);
                break;
            case 'minifigures_desc':
                $query->orderBy(['minifigures' => SORT_DESC, 'id' => SORT_ASC]);
                break;
            default:
                $query->orderBy(['year' => SORT_DESC, 'id' => SORT_ASC]);
                break;
        }
    }
}
