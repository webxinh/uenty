<?php


namespace aabc\widgets;

use aabc\base\InvalidConfigException;
use aabc\helpers\Html;
use aabc\helpers\Json;
use aabc\web\JsExpression;
use aabc\web\View;


class MaskedInput extends InputWidget
{
    
    const PLUGIN_NAME = 'inputmask';

    
    public $mask;
    
    public $definitions;
    
    public $aliases;
    
    public $clientOptions = [];
    
    public $options = ['class' => 'form-control'];
    
    public $type = 'text';

    
    protected $_hashVar;


    
    public function init()
    {
        parent::init();
        if (empty($this->mask) && empty($this->clientOptions['alias'])) {
            throw new InvalidConfigException("Either the 'mask' property or the 'clientOptions[\"alias\"]' property must be set.");
        }
    }

    
    public function run()
    {
        $this->registerClientScript();
        if ($this->hasModel()) {
            echo Html::activeInput($this->type, $this->model, $this->attribute, $this->options);
        } else {
            echo Html::input($this->type, $this->name, $this->value, $this->options);
        }
    }

    
    protected function hashPluginOptions($view)
    {
        $encOptions = empty($this->clientOptions) ? '{}' : Json::htmlEncode($this->clientOptions);
        $this->_hashVar = self::PLUGIN_NAME . '_' . hash('crc32', $encOptions);
        $this->options['data-plugin-' . self::PLUGIN_NAME] = $this->_hashVar;
        $view->registerJs("var {$this->_hashVar} = {$encOptions};", View::POS_READY);
    }

    
    protected function initClientOptions()
    {
        $options = $this->clientOptions;
        foreach ($options as $key => $value) {
            if (!$value instanceof JsExpression && in_array($key, ['oncomplete', 'onincomplete', 'oncleared', 'onKeyUp',
                    'onKeyDown', 'onBeforeMask', 'onBeforePaste', 'onUnMask', 'isComplete', 'determineActiveMasksetIndex'], true)
            ) {
                $options[$key] = new JsExpression($value);
            }
        }
        $this->clientOptions = $options;
    }

    
    public function registerClientScript()
    {
        $js = '';
        $view = $this->getView();
        $this->initClientOptions();
        if (!empty($this->mask)) {
            $this->clientOptions['mask'] = $this->mask;
        }
        $this->hashPluginOptions($view);
        if (is_array($this->definitions) && !empty($this->definitions)) {
            $js .= ucfirst(self::PLUGIN_NAME) . '.extendDefinitions(' . Json::htmlEncode($this->definitions) . ');';
        }
        if (is_array($this->aliases) && !empty($this->aliases)) {
            $js .= ucfirst(self::PLUGIN_NAME) . '.extendAliases(' . Json::htmlEncode($this->aliases) . ');';
        }
        $id = $this->options['id'];
        $js .= 'jQuery("#' . $id . '").' . self::PLUGIN_NAME . '(' . $this->_hashVar . ');';
        MaskedInputAsset::register($view);
        $view->registerJs($js);
    }
}
