<?php


namespace aabc\validators;

use aabc\web\AssetBundle;


class PunycodeAsset extends AssetBundle
{
    public $sourcePath = '@bower/punycode';
    public $js = [
        'punycode.js',
    ];
}
