<?php

namespace backend\modules\admin\models;

use common\models\SetOfferImport;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class SetOfferImportSearch extends SetOfferImport
{
    public ?string $setName = null;

    public function rules(): array
    {
        return [
            [['id', 'set_id', 'attempts', 'set_offer_id'], 'integer'],
            [['input_url', 'status', 'error_message', 'processed_at', 'created_at', 'updated_at', 'setName'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = SetOfferImport::find()
            ->alias('import')
            ->with(['set', 'setOffer'])
            ->joinWith(['set setRelation']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 30,
            ],
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
            ],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'import.id' => $this->id,
            'import.set_id' => $this->set_id,
            'import.attempts' => $this->attempts,
            'import.set_offer_id' => $this->set_offer_id,
            'import.processed_at' => $this->processed_at,
            'import.created_at' => $this->created_at,
            'import.updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'import.input_url', $this->input_url])
            ->andFilterWhere(['like', 'import.status', $this->status])
            ->andFilterWhere(['like', 'import.error_message', $this->error_message])
            ->andFilterWhere(['like', 'setRelation.name', $this->setName]);

        return $dataProvider;
    }
}
