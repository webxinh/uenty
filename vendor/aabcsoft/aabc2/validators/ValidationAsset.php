<?php


namespace aabc\validators;

use aabc\web\AssetBundle;


class ValidationAsset extends AssetBundle
{
    public $sourcePath = '@aabc/assets';
    public $js = [
        'aabc.validation.js',
    ];
    public $depends = [
        'aabc\web\AabcAsset',
    ];
}
