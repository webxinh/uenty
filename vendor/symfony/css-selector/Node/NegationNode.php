<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\CssSelector\Node;


class NegationNode extends AbstractNode
{
    
    private $selector;

    
    private $subSelector;

    
    public function __construct(NodeInterface $selector, NodeInterface $subSelector)
    {
        $this->selector = $selector;
        $this->subSelector = $subSelector;
    }

    
    public function getSelector()
    {
        return $this->selector;
    }

    
    public function getSubSelector()
    {
        return $this->subSelector;
    }

    
    public function getSpecificity()
    {
        return $this->selector->getSpecificity()->plus($this->subSelector->getSpecificity());
    }

    
    public function __toString()
    {
        return sprintf('%s[%s:not(%s)]', $this->getNodeName(), $this->selector, $this->subSelector);
    }
}
