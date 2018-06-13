<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


interface PHPUnit_Runner_TestSuiteLoader
{
    
    public function load($suiteClassName, $suiteClassFile = '');

    
    public function reload(ReflectionClass $aClass);
}
