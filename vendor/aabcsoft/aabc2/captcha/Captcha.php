<?php


namespace aabc\captcha;

use Aabc;
use aabc\base\InvalidConfigException;
use aabc\helpers\Url;
use aabc\helpers\Html;
use aabc\helpers\Json;
use aabc\widgets\InputWidget;


class Captcha extends InputWidget
{
    
    public $captchaAction = 'site/captcha';
    
    public $imageOptions = [];
    
    public $template = '{image} {input}';
    
    public $options = ['class' => 'form-control'];


    
    public function init()
    {
        parent::init();

        static::checkRequirements();

        if (!isset($this->imageOptions['id'])) {
            $this->imageOptions['id'] = $this->options['id'] . '-image';
        }
    }

    
    public function run()
    {
        $this->registerClientScript();
        if ($this->hasModel()) {
            $input = Html::activeTextInput($this->model, $this->attribute, $this->options);
        } else {
            $input = Html::textInput($this->name, $this->value, $this->options);
        }
        $route = $this->captchaAction;
        if (is_array($route)) {
            $route['v'] = uniqid();
        } else {
            $route = [$route, 'v' => uniqid()];
        }
        $image = Html::img($route, $this->imageOptions);
        echo strtr($this->template, [
            '{input}' => $input,
            '{image}' => $image,
        ]);
    }

    
    public function registerClientScript()
    {
        $options = $this->getClientOptions();
        $options = empty($options) ? '' : Json::htmlEncode($options);
        $id = $this->imageOptions['id'];
        $view = $this->getView();
        CaptchaAsset::register($view);
        $view->registerJs("jQuery('#$id').aabcCaptcha($options);");
    }

    
    protected function getClientOptions()
    {
        $route = $this->captchaAction;
        if (is_array($route)) {
            $route[CaptchaAction::REFRESH_GET_VAR] = 1;
        } else {
            $route = [$route, CaptchaAction::REFRESH_GET_VAR => 1];
        }

        $options = [
            'refreshUrl' => Url::toRoute($route),
            'hashKey' => 'aabcCaptcha/' . trim($route[0], '/'),
        ];

        return $options;
    }

    
    public static function checkRequirements()
    {
        if (extension_loaded('imagick')) {
            $imagickFormats = (new \Imagick())->queryFormats('PNG');
            if (in_array('PNG', $imagickFormats, true)) {
                return 'imagick';
            }
        }
        if (extension_loaded('gd')) {
            $gdInfo = gd_info();
            if (!empty($gdInfo['FreeType Support'])) {
                return 'gd';
            }
        }
        throw new InvalidConfigException('Either GD PHP extension with FreeType support or ImageMagick PHP extension with PNG support is required.');
    }
}
