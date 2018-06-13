<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Output;

use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;


class NullOutput implements OutputInterface
{
    
    public function setFormatter(OutputFormatterInterface $formatter)
    {
        // do nothing
    }

    
    public function getFormatter()
    {
        // to comply with the interface we must return a OutputFormatterInterface
        return new OutputFormatter();
    }

    
    public function setDecorated($decorated)
    {
        // do nothing
    }

    
    public function isDecorated()
    {
        return false;
    }

    
    public function setVerbosity($level)
    {
        // do nothing
    }

    
    public function getVerbosity()
    {
        return self::VERBOSITY_QUIET;
    }

    
    public function isQuiet()
    {
        return true;
    }

    
    public function isVerbose()
    {
        return false;
    }

    
    public function isVeryVerbose()
    {
        return false;
    }

    
    public function isDebug()
    {
        return false;
    }

    
    public function writeln($messages, $options = self::OUTPUT_NORMAL)
    {
        // do nothing
    }

    
    public function write($messages, $newline = false, $options = self::OUTPUT_NORMAL)
    {
        // do nothing
    }
}
