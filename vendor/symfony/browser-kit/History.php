<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\BrowserKit;


class History
{
    protected $stack = array();
    protected $position = -1;

    
    public function clear()
    {
        $this->stack = array();
        $this->position = -1;
    }

    
    public function add(Request $request)
    {
        $this->stack = array_slice($this->stack, 0, $this->position + 1);
        $this->stack[] = clone $request;
        $this->position = count($this->stack) - 1;
    }

    
    public function isEmpty()
    {
        return count($this->stack) == 0;
    }

    
    public function back()
    {
        if ($this->position < 1) {
            throw new \LogicException('You are already on the first page.');
        }

        return clone $this->stack[--$this->position];
    }

    
    public function forward()
    {
        if ($this->position > count($this->stack) - 2) {
            throw new \LogicException('You are already on the last page.');
        }

        return clone $this->stack[++$this->position];
    }

    
    public function current()
    {
        if (-1 == $this->position) {
            throw new \LogicException('The page history is empty.');
        }

        return clone $this->stack[$this->position];
    }
}
