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


class CombinedSelectorNode extends AbstractNode
{
    
    private $selector;

    
    private $combinator;

    
    private $subSelector;

    
    public function __construct(NodeInterface $selector, $combinator, NodeInterface $subSelector)
    {
        $this->selector = $selector;
        $this->combinator = $combinator;
        $this->subSelector = $subSelector;
    }

    
    public function getSelector()
    {
        return $this->selector;
    }

    
    public function getCombinator()
    {
        return $this->combinator;
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
        $combinator = ' ' === $this->combinator ? '<followed>' : $this->combinator;

        return sprintf('%s[%s %s %s]', $this->getNodeName(), $this->selector, $combinator, $this->subSelector);
    }
}
