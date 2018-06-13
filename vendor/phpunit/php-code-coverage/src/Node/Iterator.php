<?php
/*
 * This file is part of the php-code-coverage package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SebastianBergmann\CodeCoverage\Node;


class Iterator implements \RecursiveIterator
{
    
    private $position;

    
    private $nodes;

    
    public function __construct(Directory $node)
    {
        $this->nodes = $node->getChildNodes();
    }

    
    public function rewind()
    {
        $this->position = 0;
    }

    
    public function valid()
    {
        return $this->position < count($this->nodes);
    }

    
    public function key()
    {
        return $this->position;
    }

    
    public function current()
    {
        return $this->valid() ? $this->nodes[$this->position] : null;
    }

    
    public function next()
    {
        $this->position++;
    }

    
    public function getChildren()
    {
        return new self(
            $this->nodes[$this->position]
        );
    }

    
    public function hasChildren()
    {
        return $this->nodes[$this->position] instanceof Directory;
    }
}
