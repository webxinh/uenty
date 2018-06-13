<?php


require(__DIR__ . '/BaseAabc.php');


class Aabc extends \aabc\BaseAabc
{
}

spl_autoload_register(['Aabc', 'autoload'], true, true);
Aabc::$classMap = require(__DIR__ . '/classes.php');
Aabc::$container = new aabc\di\Container();
