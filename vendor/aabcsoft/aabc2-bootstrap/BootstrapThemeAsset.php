<?php


namespace aabc\bootstrap;

use aabc\web\AssetBundle;


class BootstrapThemeAsset extends AssetBundle
{
    public $sourcePath = '@bower/bootstrap/dist';
    public $css = [
        'css/bootstrap-theme.css',
    ];
    public $depends = [
        'aabc\bootstrap\BootstrapAsset',
    ];
}
