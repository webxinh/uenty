<?php


namespace aabc\bootstrap;

use aabc\base\InvalidConfigException;
use aabc\helpers\ArrayHelper;


class Progress extends Widget
{
    
    public $label;
    
    public $percent = 0;
    
    public $barOptions = [];
    
    public $bars;


    
    public function init()
    {
        parent::init();
        Html::addCssClass($this->options, ['widget' => 'progress']);
    }

    
    public function run()
    {
        BootstrapAsset::register($this->getView());
        return implode("\n", [
            Html::beginTag('div', $this->options),
            $this->renderProgress(),
            Html::endTag('div')
        ]) . "\n";
    }

    
    protected function renderProgress()
    {
        if (empty($this->bars)) {
            return $this->renderBar($this->percent, $this->label, $this->barOptions);
        }
        $bars = [];
        foreach ($this->bars as $bar) {
            $label = ArrayHelper::getValue($bar, 'label', '');
            if (!isset($bar['percent'])) {
                throw new InvalidConfigException("The 'percent' option is required.");
            }
            $options = ArrayHelper::getValue($bar, 'options', []);
            $bars[] = $this->renderBar($bar['percent'], $label, $options);
        }

        return implode("\n", $bars);
    }

    
    protected function renderBar($percent, $label = '', $options = [])
    {
        $defaultOptions = [
            'role' => 'progressbar',
            'aria-valuenow' => $percent,
            'aria-valuemin' => 0,
            'aria-valuemax' => 100,
            'style' => "width:{$percent}%",
        ];
        $options = array_merge($defaultOptions, $options);
        Html::addCssClass($options, ['widget' => 'progress-bar']);

        $out = Html::beginTag('div', $options);
        $out .= $label;
        $out .= Html::tag('span', \Aabc::t('aabc', '{percent}% Complete', ['percent' => $percent]), [
            'class' => 'sr-only'
        ]);
        $out .= Html::endTag('div');

        return $out;
    }
}
