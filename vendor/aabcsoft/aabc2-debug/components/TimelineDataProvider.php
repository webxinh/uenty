<?php


namespace aabc\debug\components;

use aabc\data\ArrayDataProvider;
use aabc\debug\panels\TimelinePanel;


class TimelineDataProvider extends ArrayDataProvider
{
    
    protected $panel;


    
    public function __construct(TimelinePanel $panel, $config = [])
    {
        $this->panel = $panel;
        parent::__construct($config);
    }

    
    protected function prepareModels()
    {
        if (($models = $this->allModels) === null) {
            return [];
        }
        $child = [];
        foreach ($models as $key => &$model) {
            $model['timestamp'] *= 1000;
            $model['duration'] *= 1000;
            $model['child'] = 0;
            $model['css']['width'] = $this->getWidth($model);
            $model['css']['left'] = $this->getLeft($model);
            $model['css']['color'] = $this->getColor($model);
            foreach ($child as $id => $timestamp) {
                if ($timestamp > $model['timestamp']) {
                    ++$models[$id]['child'];
                } else {
                    unset($child[$id]);
                }
            }
            $child[$key] = $model['timestamp'] + $model['duration'];
        }
        return $models;
    }

    
    public function getColor($model)
    {
        $width = isset($model['css']['width']) ? $model['css']['width'] : $this->getWidth($model);
        foreach ($this->panel->colors as $percent => $color) {
            if ($width >= $percent) {
                return $color;
            }
        }
        return '#d6e685';
    }

    
    public function getLeft($model)
    {
        return $this->getTime($model) / ($this->panel->duration / 100);
    }

    
    public function getTime($model)
    {
        return $model['timestamp'] - $this->panel->start;
    }

    
    public function getWidth($model)
    {
        return $model['duration'] / ($this->panel->duration / 100);
    }

    
    public function getCssClass($model)
    {
        $class = 'time';
        $class .= (($model['css']['left'] > 15) && ($model['css']['left'] + $model['css']['width'] > 50)) ? ' right' : ' left';
        return $class;
    }

    
    public function getRulers($line = 10)
    {
        if ($line == 0) {
            return [];
        }
        $data = [0];
        $percent = ($this->panel->duration / 100);
        $row = $this->panel->duration / $line;
        $precision = $row > 100 ? -2 : -1;
        for ($i = 1; $i < $line; $i++) {
            $ms = round($i * $row, $precision);
            $data[$ms] = $ms / $percent;
        }
        return $data;
    }

}
