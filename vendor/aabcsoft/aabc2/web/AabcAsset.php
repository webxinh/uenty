<?php


namespace aabc\web;


class AabcAsset extends AssetBundle
{
	//Cac file nguon: aabc.activeForm.js, aabc.js
    public $sourcePath = '@aabc/assets';
    public $js = [
        'aabc.js',
    ];
    public $depends = [
        'aabc\web\JqueryAsset',        
    ];
}
