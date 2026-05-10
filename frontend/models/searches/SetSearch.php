<?php

namespace frontend\models\searches;

use common\enums\StatusEnum;
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
    public ?string $tag_slug = null;
    public $month = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'theme_id', 'status', 'number_variant', 'minifigures', 'year', 'month', 'pieces', 'released', 'price', 'age', 'subtheme_id'], 'integer'],
            [['number', 'name', 'slug', 'brickset_url', 'created_at', 'updated_at', 'sort_option', 'tag_slug'], 'safe'],
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
        $query = Set::find()
            ->andFilterCompare('{{%set}}.status', StatusEnum::ACTIVE->value);

        $this->joinActiveTheme($query);

        $this->load($params);

        if (!$this->validate()) {
            return $this->buildQuery($query);
        }

        if ($this->subtheme_id) {
            $query->andWhere(['subtheme_id' => $this->subtheme_id]);
        }

        if ($this->theme_id && $this->subtheme_id) {
            $query->andWhere(['theme_id' => $this->theme_id]);
        }

        if ($this->theme_id && !$this->subtheme_id) {
            $activeThemeIds = Theme::find()->select('id')->where(['name' => $this->theme->name])->andWhere(['status' => StatusEnum::ACTIVE->value]);
            $query->andWhere([
                'or',
                ['theme_id' => $activeThemeIds],
                ['subtheme_id' => $activeThemeIds],
            ]);
        }

        $query->andFilterWhere([
            'year' => $this->year,
        ]);

        $this->applyNameFilter($query);

        if ($this->tag_slug) {
            $query->innerJoin('{{%set_tag}} st_tag', 'st_tag.set_id = {{%set}}.id')
                  ->innerJoin('{{%tag}} t_tag', 't_tag.id = st_tag.tag_id')
                  ->andWhere(['t_tag.slug' => $this->tag_slug]);
        }

        $this->applySortOption($query);

        return $this->buildQuery($query);
    }

    public function searchNew(array $params = []): ActiveDataProvider
    {
        $query = Set::find()
            ->alias('s')
            ->andWhere(['s.status' => StatusEnum::ACTIVE->value])
            ->andWhere(['not', ['s.release_date' => null]])
            ->orderBy('s.release_date DESC, s.id DESC');

        $this->joinActiveTheme($query, 's');

        $this->load($params);

        $this->applyNameFilter($query, 's');

        if ($this->year) {
            $query->andWhere(new Expression('YEAR(s.release_date) = :year', [':year' => (int)$this->year]));
        }

        if ($this->month) {
            $query->andWhere(new Expression('MONTH(s.release_date) = :month', [':month' => (int)$this->month]));
        }

        return new ActiveDataProvider([
            'query'      => $query,
            'pagination' => [
                'pageSize' => 48,
                'pageParam' => 'new_page',
            ],
        ]);
    }

    public static function getMonthOptions(): array
    {
        return [
            1  => T::tr('January'),
            2  => T::tr('February'),
            3  => T::tr('March'),
            4  => T::tr('April'),
            5  => T::tr('May'),
            6  => T::tr('June'),
            7  => T::tr('July'),
            8  => T::tr('August'),
            9  => T::tr('September'),
            10 => T::tr('October'),
            11 => T::tr('November'),
            12 => T::tr('December'),
        ];
    }

    public function searchPromo(): ActiveDataProvider
    {
        $subquery = '(SELECT set_id, MIN(price) as min_price FROM {{%set_offer}} WHERE currency_code = \'USD\' AND price > 0 GROUP BY set_id)';

        $query = Set::find()
            ->alias('s')
            ->select(['s.*'])
            ->andWhere(['s.status' => StatusEnum::ACTIVE->value])
            ->andWhere(['not', ['s.price' => null]])
            ->andWhere(['>', 's.price', 0])
            ->innerJoin($subquery . ' so', 'so.set_id = s.id AND so.min_price < s.price')
            ->orderBy(new Expression('(s.price - so.min_price) / s.price DESC'));

        $this->joinActiveTheme($query, 's');

        return new ActiveDataProvider([
            'query'      => $query,
            'pagination' => [
                'pageSize'  => 20,
                'pageParam' => 'promo_page',
            ],
        ]);
    }

    private function joinActiveTheme($query, string $setAlias = ''): void
    {
        $setRef = $setAlias ? "$setAlias.theme_id" : '{{%set}}.theme_id';
        $query->innerJoin('{{%theme}} t_theme', "t_theme.id = $setRef AND t_theme.status = " . StatusEnum::ACTIVE->value);
    }

    private function buildQuery($query): ActiveDataProvider
    {
        return new ActiveDataProvider([
            'query'      => $query,
            //'sort'  => ['defaultOrder' => ['year' => SORT_DESC, 'id' => SORT_ASC]], //moved to getSortOptions()
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
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

    private function applyNameFilter($query, string $alias = ''): void
    {
        if (!$this->name) {
            return;
        }

        $col = fn(string $c) => $alias ? "$alias.$c" : "{{%set}}.$c";

        if (is_numeric($this->name)) {
            $query->andWhere([$col('number') => $this->name]);
        } else {
            $themeIdsQuery = Theme::find()
                ->alias('t')
                ->select('t.id')
                ->where(['like', 't.name', $this->name])
                ->andWhere(['t.status' => StatusEnum::ACTIVE->value]);

            $query->andWhere([
                'or',
                ['like', $col('name'), $this->name],
                [$col('theme_id') => $themeIdsQuery],
                [$col('subtheme_id') => $themeIdsQuery],
            ]);
        }
    }

    private function applySortOption($query): void
    {
        switch ($this->sort_option) {
            case 'price_asc':
                $query->orderBy(new Expression('price IS NULL ASC, price ASC, id ASC'));
                break;
            case 'price_desc':
                $query->orderBy(new Expression('price IS NULL ASC, price DESC, id ASC'));
                break;
            case 'pieces_asc':
                $query->orderBy(new Expression('pieces IS NULL ASC, pieces ASC, id ASC'));
                break;
            case 'pieces_desc':
                $query->orderBy(new Expression('pieces IS NULL ASC, pieces DESC, id ASC'));
                break;
            case 'year_desc':
                $query->orderBy(new Expression('year IS NULL ASC, year DESC, release_date IS NULL ASC, release_date DESC, id ASC'));
                break;
            case 'year_asc':
                $query->orderBy(new Expression('year IS NULL ASC, year ASC, release_date IS NULL ASC, release_date ASC, id ASC'));
                break;
            case 'name_asc':
                $query->orderBy(['name' => SORT_ASC, 'id' => SORT_ASC]);
                break;
            case 'name_desc':
                $query->orderBy(['name' => SORT_DESC, 'id' => SORT_ASC]);
                break;
            case 'price_per_piece_asc':
                $query->orderBy(new Expression('(pieces IS NULL OR pieces <= 0 OR price IS NULL) ASC, CASE WHEN pieces > 0 THEN price / pieces END ASC, id ASC'));
                break;
            case 'price_per_piece_desc':
                $query->orderBy(new Expression('(pieces IS NULL OR pieces <= 0 OR price IS NULL) ASC, CASE WHEN pieces > 0 THEN price / pieces END DESC, id ASC'));
                break;
            case 'minifigures_asc':
                $query->orderBy(new Expression('minifigures IS NULL ASC, minifigures ASC, id ASC'));
                break;
            case 'minifigures_desc':
                $query->orderBy(new Expression('minifigures IS NULL ASC, minifigures DESC, id ASC'));
                break;
            default:
                $query->orderBy(new Expression(
                    'EXISTS (SELECT 1 FROM {{%set_offer}} so WHERE so.[[set_id]] = {{%set}}.[[id]]) DESC, COALESCE({{%set}}.[[release_date]], MAKEDATE({{%set}}.[[year]], 365)) DESC, {{%set}}.[[id]] ASC'
                ));
                break;
        }
    }
}
