<?php
namespace dosamigos\datepicker;
use aabc\web\AssetBundle;

class DateRangePickerAsset extends AssetBundle
{
    public $sourcePath = '@vendor/2amigos/date-picker-widget/src/assets';

    public $css = [
        'css/bootstrap-daterangepicker.css'
    ];

    public $depends = [
        'dosamigos\datepicker\DatePickerAsset'
    ];

}
