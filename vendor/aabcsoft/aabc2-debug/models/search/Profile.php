<?php


namespace aabc\debug\models\search;

use aabc\data\ArrayDataProvider;
use aabc\debug\components\search\Filter;


class Profile extends Base
{
    
    public $category;
    
    public $info;


    
    public function rules()
    {
        return [
            [['category', 'info'], 'safe'],
        ];
    }

    
    public function attributeLabels()
    {
        return [
            'category' => 'Category',
            'info' => 'Info',
        ];
    }

    
    public function search($params, $models)
    {
        $dataProvider = new ArrayDataProvider([
            'allModels' => $models,
            'pagination' => false,
            'sort' => [
                'attributes' => ['category', 'seq', 'duration', 'info'],
                'defaultOrder' => [
                    'duration' => SORT_DESC,
                ],
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $filter = new Filter();
        $this->addCondition($filter, 'category', true);
        $this->addCondition($filter, 'info', true);
        $dataProvider->allModels = $filter->filter($models);

        return $dataProvider;
    }
}
