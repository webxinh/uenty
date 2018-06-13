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


class PseudoNode extends AbstractNode
{
    
    private $selector;

    
    private $identifier;

    
    public function __construct(NodeInterface $selector, $identifier)
    {
        $this->selector = $selector;
        $this->identifier = strtolower($identifier);
    }

    
    public function getSelector()
    {
        return $this->selector;
    }

    
    public function getIdentifier()
    {
        return $this->identifier;
    }

    
    public function getSpecificity()
    {
        return $this->selector->getSpecificity()->plus(new Specificity(0, 1, 0));
    }

    
    public function __toString()
    {
        return sprintf('%s[%s:%s]', $this->getNodeName(), $this->selector, $this->identifier);
    }
}
