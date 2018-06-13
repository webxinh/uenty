<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Helper;

use Symfony\Component\Console\Exception\InvalidArgumentException;


class TableCell
{
    
    private $value;

    
    private $options = array(
        'rowspan' => 1,
        'colspan' => 1,
    );

    
    public function __construct($value = '', array $options = array())
    {
        $this->value = $value;

        // check option names
        if ($diff = array_diff(array_keys($options), array_keys($this->options))) {
            throw new InvalidArgumentException(sprintf('The TableCell does not support the following options: \'%s\'.', implode('\', \'', $diff)));
        }

        $this->options = array_merge($this->options, $options);
    }

    
    public function __toString()
    {
        return $this->value;
    }

    
    public function getColspan()
    {
        return (int) $this->options['colspan'];
    }

    
    public function getRowspan()
    {
        return (int) $this->options['rowspan'];
    }
}
