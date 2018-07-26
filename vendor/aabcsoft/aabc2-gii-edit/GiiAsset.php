<?php


namespace aabc\gii;

use aabc\web\AssetBundle;


class GiiAsset extends AssetBundle
{
    public $sourcePath = '@aabc/gii/assets';
    public $css = [
        'main.css',
    ];
    public $js = [
        'gii.js',
    ];
    public $depends = [
        'aabc\web\AabcAsset',
        'aabc\bootstrap\BootstrapAsset',
        'aabc\bootstrap\BootstrapPluginAsset',
        'aabc\gii\TypeAheadAsset',
    ];
}
