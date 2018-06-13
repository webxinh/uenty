<?php


namespace aabc\debug;

use aabc\web\AssetBundle;


class TimelineAsset extends AssetBundle
{
    public $sourcePath = '@aabc/debug/assets';
    public $css = [
        'timeline.css',
    ];
    public $js = [
        'timeline.js',
    ];
}