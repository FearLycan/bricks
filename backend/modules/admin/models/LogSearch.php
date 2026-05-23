<?php

namespace backend\modules\admin\models;

use common\models\Log;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class LogSearch extends Log
{
    public string $resolved = '';
    public string $createdFrom = '';
    public string $createdTo = '';

    public function rules(): array
    {
        return [
            [['id'], 'integer'],
            [['level', 'category', 'source', 'prefix', 'message', 'created_at', 'resolved_at', 'resolved', 'createdFrom', 'createdTo'], 'safe'],
        ];
    }

    public function scenarios(): array
    {
        return Model::scenarios();
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = Log::find();

        $this->load($params);
        if (!$this->validate()) {
            return $this->buildProvider($query);
        }

        $query->andFilterWhere([
            'id'       => $this->id,
            'level'    => $this->level,
            'source'   => $this->source,
        ]);

        $query->andFilterWhere(['like', 'category', $this->category])
            ->andFilterWhere(['like', 'prefix', $this->prefix])
            ->andFilterWhere(['like', 'message', $this->message]);

        if ($this->resolved === '1') {
            $query->andWhere(['not', ['resolved_at' => null]]);
        } elseif ($this->resolved === '0') {
            $query->andWhere(['resolved_at' => null]);
        }

        if ($this->createdFrom !== '') {
            $query->andWhere(['>=', 'created_at', $this->createdFrom]);
        }
        if ($this->createdTo !== '') {
            $query->andWhere(['<=', 'created_at', $this->createdTo]);
        }

        return $this->buildProvider($query);
    }

    private function buildProvider($query): ActiveDataProvider
    {
        return new ActiveDataProvider([
            'query'      => $query,
            'pagination' => [
                'pageSize' => 30,
            ],
            'sort'       => [
                'defaultOrder' => ['id' => SORT_DESC],
                'attributes'   => ['id', 'level', 'category', 'source', 'created_at', 'resolved_at'],
            ],
        ]);
    }
}
