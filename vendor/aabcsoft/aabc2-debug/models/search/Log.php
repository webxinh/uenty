<?php


namespace aabc\debug\models\search;

use aabc\data\ArrayDataProvider;
use aabc\debug\components\search\Filter;


class Log extends Base
{
    
    public $level;
    
    public $category;
    
    public $message;


    
    public function rules()
    {
        return [
            [['level', 'message', 'category'], 'safe'],
        ];
    }

    
    public function attributeLabels()
    {
        return [
            'level' => 'Level',
            'category' => 'Category',
            'message' => 'Message',
        ];
    }

    
    public function search($params, $models)
    {
        $dataProvider = new ArrayDataProvider([
            'allModels' => $models,
            'pagination' => false,
            'sort' => [
                'attributes' => ['time', 'level', 'category', 'message'],
                'defaultOrder' => [
                    'time' => SORT_ASC,
                ],
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $filter = new Filter();
        $this->addCondition($filter, 'level');
        $this->addCondition($filter, 'category', true);
        $this->addCondition($filter, 'message', true);
        $dataProvider->allModels = $filter->filter($models);

        return $dataProvider;
    }
}
