<?php


namespace aabc\bootstrap;

use Aabc;
use aabc\helpers\ArrayHelper;


class Alert extends Widget
{
    
    public $body;
    
    public $closeButton = [];


    
    public function init()
    {
        parent::init();

        $this->initOptions();

        echo Html::beginTag('div', $this->options) . "\n";
        echo $this->renderBodyBegin() . "\n";
    }

    
    public function run()
    {
        echo "\n" . $this->renderBodyEnd();
        echo "\n" . Html::endTag('div');

        $this->registerPlugin('alert');
    }

    
    protected function renderBodyBegin()
    {
        return $this->renderCloseButton();
    }

    
    protected function renderBodyEnd()
    {
        return $this->body . "\n";
    }

    
    protected function renderCloseButton()
    {
        if (($closeButton = $this->closeButton) !== false) {
            $tag = ArrayHelper::remove($closeButton, 'tag', 'button');
            $label = ArrayHelper::remove($closeButton, 'label', '&times;');
            if ($tag === 'button' && !isset($closeButton['type'])) {
                $closeButton['type'] = 'button';
            }

            return Html::tag($tag, $label, $closeButton);
        } else {
            return null;
        }
    }

    
    protected function initOptions()
    {
        Html::addCssClass($this->options, ['alert', 'fade', 'in']);

        if ($this->closeButton !== false) {
            $this->closeButton = array_merge([
                'data-dismiss' => 'alert',
                'aria-hidden' => 'true',
                'class' => 'close',
            ], $this->closeButton);
        }
    }
}
