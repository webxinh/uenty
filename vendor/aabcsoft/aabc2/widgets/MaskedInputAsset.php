<?php


namespace aabc\widgets;

use aabc\web\AssetBundle;


class MaskedInputAsset extends AssetBundle
{
    public $sourcePath = '@bower/jquery.inputmask/dist';
    public $js = [
        'jquery.inputmask.bundle.js'
    ];
    public $depends = [
        'aabc\web\AabcAsset'
    ];
}
