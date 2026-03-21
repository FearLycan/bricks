<?php

namespace backend\modules\admin\models;

use common\models\User;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class UserSearch extends User
{
    public function rules(): array
    {
        return [
            [['id', 'status'], 'integer'],
            [['username', 'email', 'role', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = User::find();
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

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['role' => $this->role]);

        return $dataProvider;
    }
}
