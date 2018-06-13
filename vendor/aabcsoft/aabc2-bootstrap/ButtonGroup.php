<?php


namespace aabc\bootstrap;

use aabc\helpers\ArrayHelper;


class ButtonGroup extends Widget
{
    
    public $buttons = [];
    
    public $encodeLabels = true;


    
    public function init()
    {
        parent::init();
        Html::addCssClass($this->options, ['widget' => 'btn-group']);
    }

    
    public function run()
    {
        BootstrapAsset::register($this->getView());
        return Html::tag('div', $this->renderButtons(), $this->options);
    }

    
    protected function renderButtons()
    {
        $buttons = [];
        foreach ($this->buttons as $button) {
            if (is_array($button)) {
                $visible = ArrayHelper::remove($button, 'visible', true);
                if ($visible === false) {
                    continue;
                }

                $button['view'] = $this->getView();
                if (!isset($button['encodeLabel'])) {
                    $button['encodeLabel'] = $this->encodeLabels;
                }
                $buttons[] = Button::widget($button);
            } else {
                $buttons[] = $button;
            }
        }

        return implode("\n", $buttons);
    }
}
