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

use Symfony\Component\CssSelector\Parser\Token;


class FunctionNode extends AbstractNode
{
    
    private $selector;

    
    private $name;

    
    private $arguments;

    
    public function __construct(NodeInterface $selector, $name, array $arguments = array())
    {
        $this->selector = $selector;
        $this->name = strtolower($name);
        $this->arguments = $arguments;
    }

    
    public function getSelector()
    {
        return $this->selector;
    }

    
    public function getName()
    {
        return $this->name;
    }

    
    public function getArguments()
    {
        return $this->arguments;
    }

    
    public function getSpecificity()
    {
        return $this->selector->getSpecificity()->plus(new Specificity(0, 1, 0));
    }

    
    public function __toString()
    {
        $arguments = implode(', ', array_map(function (Token $token) {
            return "'".$token->getValue()."'";
        }, $this->arguments));

        return sprintf('%s[%s:%s(%s)]', $this->getNodeName(), $this->selector, $this->name, $arguments ? '['.$arguments.']' : '');
    }
}
