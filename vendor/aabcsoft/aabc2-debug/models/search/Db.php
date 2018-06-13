<?php


namespace aabc\debug\models\search;

use aabc\data\ArrayDataProvider;
use aabc\debug\components\search\Filter;


class Db extends Base
{
    
    public $type;
    
    public $query;


    
    public function rules()
    {
        return [
            [['type', 'query'], 'safe'],
        ];
    }

    
    public function attributeLabels()
    {
        return [
            'type' => 'Type',
            'query' => 'Query',
        ];
    }

    
    public function search($models)
    {
        $dataProvider = new ArrayDataProvider([
            'allModels' => $models,
            'pagination' => false,
            'sort' => [
                'attributes' => ['duration', 'seq', 'type', 'query'],
            ],
        ]);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $filter = new Filter();
        $this->addCondition($filter, 'type', true);
        $this->addCondition($filter, 'query', true);
        $dataProvider->allModels = $filter->filter($models);

        return $dataProvider;
    }
}
