<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Util_Printer
{
    
    protected $autoFlush = false;

    
    protected $out;

    
    protected $outTarget;

    
    public function __construct($out = null)
    {
        if ($out !== null) {
            if (is_string($out)) {
                if (strpos($out, 'socket://') === 0) {
                    $out = explode(':', str_replace('socket://', '', $out));

                    if (sizeof($out) != 2) {
                        throw new PHPUnit_Framework_Exception;
                    }

                    $this->out = fsockopen($out[0], $out[1]);
                } else {
                    if (strpos($out, 'php://') === false &&
                        !is_dir(dirname($out))) {
                        mkdir(dirname($out), 0777, true);
                    }

                    $this->out = fopen($out, 'wt');
                }

                $this->outTarget = $out;
            } else {
                $this->out = $out;
            }
        }
    }

    
    public function flush()
    {
        if ($this->out && strncmp($this->outTarget, 'php://', 6) !== 0) {
            fclose($this->out);
        }
    }

    
    public function incrementalFlush()
    {
        if ($this->out) {
            fflush($this->out);
        } else {
            flush();
        }
    }

    
    public function write($buffer)
    {
        if ($this->out) {
            fwrite($this->out, $buffer);

            if ($this->autoFlush) {
                $this->incrementalFlush();
            }
        } else {
            if (PHP_SAPI != 'cli' && PHP_SAPI != 'phpdbg') {
                $buffer = htmlspecialchars($buffer, ENT_SUBSTITUTE);
            }

            print $buffer;

            if ($this->autoFlush) {
                $this->incrementalFlush();
            }
        }
    }

    
    public function getAutoFlush()
    {
        return $this->autoFlush;
    }

    
    public function setAutoFlush($autoFlush)
    {
        if (is_bool($autoFlush)) {
            $this->autoFlush = $autoFlush;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
    }
}
