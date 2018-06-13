<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Util_PHP_Windows extends PHPUnit_Util_PHP_Default
{
    protected $useTempFile = true;

    protected function getHandles()
    {
        if (false === $stdout_handle = tmpfile()) {
            throw new PHPUnit_Framework_Exception(
                'A temporary file could not be created; verify that your TEMP environment variable is writable'
            );
        }

        return [
            1 => $stdout_handle
        ];
    }

    public function getCommand(array $settings, $file = null)
    {
        return '"' . parent::getCommand($settings, $file) . '"';
    }
}
