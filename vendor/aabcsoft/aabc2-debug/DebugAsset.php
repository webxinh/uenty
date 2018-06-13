<?php


namespace aabc\debug;

use aabc\web\AssetBundle;


class DebugAsset extends AssetBundle
{
    public $sourcePath = '@aabc/debug/assets';
    public $css = [
        'main.css',
        'toolbar.css',
    ];
    public $depends = [
        'aabc\web\AabcAsset',
        'aabc\bootstrap\BootstrapAsset',
    ];
}
