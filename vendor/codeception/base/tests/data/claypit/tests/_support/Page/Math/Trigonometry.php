<?php
namespace Page\Math;

class Trigonometry
{
    // include url of current page
    public static $URL = '';

    

    
    public static function route($param)
    {
        return static::$URL.$param;
    }

    
    protected $mathTester;

    public function __construct(\MathTester $I)
    {
        $this->mathTester = $I;
    }

    public function tan($arg)
    {
        $this->mathTester->expect('i get tan of '.$arg);
        return tan($arg);
    }

    public function assertTanIsLessThen($tan, $val)
    {
        $this->mathTester->assertLessThan($val, $this->tan($tan));

    }

}