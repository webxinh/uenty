<?php
/**
 * @copyright Copyright (c) 2013-2017 2amigOS! Consulting Group LLC
 * @link http://2amigos.us
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
namespace dosamigos\datetimepicker;

use aabc\web\AssetBundle;

/**
 * DateTimePickerAsset
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @link http://www.ramirezcobos.com/
 * @link http://www.2amigos.us/
 * @package dosamigos\datetimepicker
 */
class DateTimePickerAsset extends AssetBundle
{
    public $sourcePath = '@bower/bootstrap-datetimepicker';

    public $css = [
        'css/bootstrap-datetimepicker.css'
    ];

    public $js = [
        'js/bootstrap-datetimepicker.js'
    ];

    public $depends = [
        //'aabc\bootstrap\BootstrapPluginAsset'
    ];
}
