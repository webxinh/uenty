<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Util_Filesystem
{
    
    protected static $buffer = [];

    
    public static function classNameToFilename($className)
    {
        return str_replace(
            ['_', '\\'],
            DIRECTORY_SEPARATOR,
            $className
        ) . '.php';
    }
}
