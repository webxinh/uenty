<?php


namespace aabc\debug;

use Aabc;
use aabc\base\Component;
use aabc\helpers\ArrayHelper;
use aabc\helpers\Url;


class Panel extends Component
{
    
    public $id;
    
    public $tag;
    
    public $module;
    
    public $data;
    
    public $actions = [];


    
    public function getName()
    {
        return '';
    }

    
    public function getSummary()
    {
        return '';
    }

    
    public function getDetail()
    {
        return '';
    }

    
    public function save()
    {
        return null;
    }

    
    public function load($data)
    {
        $this->data = $data;
    }

    
    public function getUrl($additionalParams = null)
    {
        $route = [
            '/' . $this->module->id . '/default/view',
            'panel' => $this->id,
            'tag' => $this->tag,
        ];

        if (is_array($additionalParams)){
            $route = ArrayHelper::merge($route, $additionalParams);
        }

        return Url::toRoute($route);
    }

    
    public function getTraceLine($options)
    {
        if (!isset($options['text'])) {
            $options['text'] = "{$options['file']}:{$options['line']}";
        }
        $traceLine = $this->module->traceLine;
        if ($traceLine === false) {
            return $options['text'];
        } else {
            $options['file'] = str_replace('\\', '/', $options['file']);
            $rawLink = $traceLine instanceof \Closure ? call_user_func($traceLine, $options, $this) : $traceLine;
            return strtr($rawLink, ['{file}' => $options['file'], '{line}' => $options['line'], '{text}' => $options['text']]);
        }
    }
}
