<?php

namespace dosamigos\datepicker;

use aabc\web\AssetBundle;


class DatePickerLanguageAsset extends AssetBundle
{
    public $sourcePath = '@bower/bootstrap-datepicker/dist/locales';

    public $depends = [
        'dosamigos\datepicker\DateRangePickerAsset'
    ];
}
