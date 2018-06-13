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


class AttributeNode extends AbstractNode
{
    
    private $selector;

    
    private $namespace;

    
    private $attribute;

    
    private $operator;

    
    private $value;

    
    public function __construct(NodeInterface $selector, $namespace, $attribute, $operator, $value)
    {
        $this->selector = $selector;
        $this->namespace = $namespace;
        $this->attribute = $attribute;
        $this->operator = $operator;
        $this->value = $value;
    }

    
    public function getSelector()
    {
        return $this->selector;
    }

    
    public function getNamespace()
    {
        return $this->namespace;
    }

    
    public function getAttribute()
    {
        return $this->attribute;
    }

    
    public function getOperator()
    {
        return $this->operator;
    }

    
    public function getValue()
    {
        return $this->value;
    }

    
    public function getSpecificity()
    {
        return $this->selector->getSpecificity()->plus(new Specificity(0, 1, 0));
    }

    
    public function __toString()
    {
        $attribute = $this->namespace ? $this->namespace.'|'.$this->attribute : $this->attribute;

        return 'exists' === $this->operator
            ? sprintf('%s[%s[%s]]', $this->getNodeName(), $this->selector, $attribute)
            : sprintf("%s[%s[%s %s '%s']]", $this->getNodeName(), $this->selector, $attribute, $this->operator, $this->value);
    }
}
