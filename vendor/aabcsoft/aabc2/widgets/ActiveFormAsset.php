<?php


namespace aabc\widgets;

use aabc\web\AssetBundle;


class ActiveFormAsset extends AssetBundle
{
    public $sourcePath = '@aabc/assets';
    public $js = [
        'aabc.activeForm.js',
    ];
    public $depends = [
        'aabc\web\AabcAsset',
    ];
}
