<?php


namespace aabc\web;

use aabc\base\Object;
use aabc\helpers\ArrayHelper;
use aabc\helpers\Url;
use Aabc;


class AssetBundle extends Object
{
    
    public $sourcePath;
    
    public $basePath;
    
    public $baseUrl;
    
    public $depends = [];
    
    public $js = [];
    
    public $css = [];
    
    public $jsOptions = [];
    
    public $cssOptions = [];
    
    public $publishOptions = [];


    
    public static function register($view)
    {
        return $view->registerAssetBundle(get_called_class());
    }

    
    public function init()
    {
        if ($this->sourcePath !== null) {
            $this->sourcePath = rtrim(Aabc::getAlias($this->sourcePath), '/\\');
        }
        if ($this->basePath !== null) {
            $this->basePath = rtrim(Aabc::getAlias($this->basePath), '/\\');
        }
        if ($this->baseUrl !== null) {
            $this->baseUrl = rtrim(Aabc::getAlias($this->baseUrl), '/');
        }
    }

    
    public function registerAssetFiles($view)
    {
        $manager = $view->getAssetManager();
        foreach ($this->js as $js) {
            if (is_array($js)) {
                $file = array_shift($js);
                $options = ArrayHelper::merge($this->jsOptions, $js);
                $view->registerJsFile($manager->getAssetUrl($this, $file), $options);
            } else {
                if ($js !== null) {
                    $view->registerJsFile($manager->getAssetUrl($this, $js), $this->jsOptions);
                }
            }
        }
        foreach ($this->css as $css) {
            if (is_array($css)) {
                $file = array_shift($css);
                $options = ArrayHelper::merge($this->cssOptions, $css);
                $view->registerCssFile($manager->getAssetUrl($this, $file), $options);
            } else {
                if ($css !== null) {
                    $view->registerCssFile($manager->getAssetUrl($this, $css), $this->cssOptions);
                }
            }
        }
    }

    
    public function publish($am)
    {
        if ($this->sourcePath !== null && !isset($this->basePath, $this->baseUrl)) {
            list ($this->basePath, $this->baseUrl) = $am->publish($this->sourcePath, $this->publishOptions);
        }

        if (isset($this->basePath, $this->baseUrl) && ($converter = $am->getConverter()) !== null) {
            foreach ($this->js as $i => $js) {
                if (is_array($js)) {
                    $file = array_shift($js);
                    if (Url::isRelative($file)) {
                        $js = ArrayHelper::merge($this->jsOptions, $js);
                        array_unshift($js, $converter->convert($file, $this->basePath));
                        $this->js[$i] = $js;
                    }
                } elseif (Url::isRelative($js)) {
                    $this->js[$i] = $converter->convert($js, $this->basePath);
                }
            }
            foreach ($this->css as $i => $css) {
                if (is_array($css)) {
                    $file = array_shift($css);
                    if (Url::isRelative($file)) {
                        $css = ArrayHelper::merge($this->cssOptions, $css);
                        array_unshift($css, $converter->convert($file, $this->basePath));
                        $this->css[$i] = $css;
                    }
                } elseif (Url::isRelative($css)) {
                    $this->css[$i] = $converter->convert($css, $this->basePath);
                }
            }
        }
    }
}
