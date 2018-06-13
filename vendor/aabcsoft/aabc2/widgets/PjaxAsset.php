<?php


namespace aabc\widgets;

use aabc\web\AssetBundle;


class PjaxAsset extends AssetBundle
{
    public $sourcePath = '@bower/aabc2-pjax';
    public $js = [
        'jquery.pjax.js',
    ];
    public $depends = [
        'aabc\web\AabcAsset',
    ];
}
