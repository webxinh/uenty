<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Util_Fileloader
{
    
    public static function checkAndLoad($filename)
    {
        $includePathFilename = stream_resolve_include_path($filename);

        if (!$includePathFilename || !is_readable($includePathFilename)) {
            throw new PHPUnit_Framework_Exception(
                sprintf('Cannot open file "%s".' . "\n", $filename)
            );
        }

        self::load($includePathFilename);

        return $includePathFilename;
    }

    
    public static function load($filename)
    {
        $oldVariableNames = array_keys(get_defined_vars());

        include_once $filename;

        $newVariables     = get_defined_vars();
        $newVariableNames = array_diff(
            array_keys($newVariables),
            $oldVariableNames
        );

        foreach ($newVariableNames as $variableName) {
            if ($variableName != 'oldVariableNames') {
                $GLOBALS[$variableName] = $newVariables[$variableName];
            }
        }

        return $filename;
    }
}
