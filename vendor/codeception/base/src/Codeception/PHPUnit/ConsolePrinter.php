<?php
namespace Codeception\PHPUnit;


interface ConsolePrinter
{
    public function write($buffer);

    public function printResult(\PHPUnit_Framework_TestResult $result);
}
