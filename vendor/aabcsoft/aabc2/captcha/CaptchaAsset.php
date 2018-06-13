<?php


namespace aabc\captcha;

use aabc\web\AssetBundle;


class CaptchaAsset extends AssetBundle
{
    public $sourcePath = '@aabc/assets';
    public $js = [
        'aabc.captcha.js',
    ];
    public $depends = [
        'aabc\web\AabcAsset',
    ];
}
