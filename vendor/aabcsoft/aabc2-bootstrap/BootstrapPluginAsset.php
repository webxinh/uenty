<?php


namespace aabc\bootstrap;

use aabc\web\AssetBundle;


class BootstrapPluginAsset extends AssetBundle
{
    public $sourcePath = '@bower/bootstrap/dist';
    public $js = [
        'js/bootstrap.js',
    ];
    public $depends = [
        'aabc\web\JqueryAsset',
        'aabc\bootstrap\BootstrapAsset',
    ];
}
