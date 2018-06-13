<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Formatter;


interface OutputFormatterInterface
{
    
    public function setDecorated($decorated);

    
    public function isDecorated();

    
    public function setStyle($name, OutputFormatterStyleInterface $style);

    
    public function hasStyle($name);

    
    public function getStyle($name);

    
    public function format($message);
}
