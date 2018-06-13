<?php


namespace aabc\grid;

use aabc\web\AssetBundle;


class GridViewAsset extends AssetBundle
{
    public $sourcePath = '@aabc/assets';
    public $js = [
        'aabc.gridView.js',
    ];
    public $depends = [
        'aabc\web\AabcAsset',
    ];
}
