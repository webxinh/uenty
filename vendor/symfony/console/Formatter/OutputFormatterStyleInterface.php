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


interface OutputFormatterStyleInterface
{
    
    public function setForeground($color = null);

    
    public function setBackground($color = null);

    
    public function setOption($option);

    
    public function unsetOption($option);

    
    public function setOptions(array $options);

    
    public function apply($text);
}
