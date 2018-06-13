<?php


namespace aabc\widgets;

use Aabc;
use aabc\base\InvalidConfigException;
use aabc\base\Widget;
use aabc\data\Sort;
use aabc\helpers\Html;


class LinkSorter extends Widget
{
    
    public $sort;
    
    public $attributes;
    
    public $options = ['class' => 'sorter'];
    
    public $linkOptions = [];


    
    public function init()
    {
        if ($this->sort === null) {
            throw new InvalidConfigException('The "sort" property must be set.');
        }
    }

    
    public function run()
    {
        echo $this->renderSortLinks();
    }

    
    protected function renderSortLinks()
    {
        $attributes = empty($this->attributes) ? array_keys($this->sort->attributes) : $this->attributes;
        $links = [];
        foreach ($attributes as $name) {
            $links[] = $this->sort->link($name, $this->linkOptions);
        }

        return Html::ul($links, array_merge($this->options, ['encode' => false]));
    }
}
