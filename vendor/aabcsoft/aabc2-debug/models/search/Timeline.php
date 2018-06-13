<?php


namespace aabc\debug\models\search;

use aabc\debug\components\search\Filter;
use aabc\debug\components\search\matchers\GreaterThanOrEqual;
use aabc\debug\components\TimelineDataProvider;
use aabc\debug\panels\TimelinePanel;


class Timeline extends Base
{
    
    public $category;
    
    public $duration = 0;


    
    public function rules()
    {
        return [
            [['category', 'duration'], 'safe'],
        ];
    }

    
    public function attributeLabels()
    {
        return [
            'duration' => 'Duration ≥'
        ];
    }

    
    public function search($params, $panel)
    {
        $models = $panel->models;
        $dataProvider = new TimelineDataProvider($panel, [
            'allModels' => $models,
            'sort' => [
                'attributes' => ['category', 'timestamp']
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $filter = new Filter();
        $this->addCondition($filter, 'category', true);
        if ($this->duration > 0) {
            $filter->addMatcher('duration', new GreaterThanOrEqual(['value' => $this->duration / 1000]));
        }
        $dataProvider->allModels = $filter->filter($models);

        return $dataProvider;
    }

}
