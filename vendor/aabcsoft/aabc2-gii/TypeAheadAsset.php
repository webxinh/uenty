<?php


namespace aabc\gii;

use aabc\web\AssetBundle;


class TypeAheadAsset extends AssetBundle
{
    public $sourcePath = '@bower/typeahead.js/dist';
    public $js = [
        'typeahead.bundle.js',
    ];
    public $depends = [
        'aabc\bootstrap\BootstrapAsset',
        'aabc\bootstrap\BootstrapPluginAsset',
    ];
}
