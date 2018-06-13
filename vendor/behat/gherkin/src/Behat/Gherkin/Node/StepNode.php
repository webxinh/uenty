<?php

/*
 * This file is part of the Behat Gherkin.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Gherkin\Node;

use Behat\Gherkin\Exception\NodeException;


class StepNode implements NodeInterface
{
    
    private $keyword;
    
    private $keywordType;
    
    private $text;
    
    private $arguments = array();
    
    private $line;

    
    public function __construct($keyword, $text, array $arguments, $line, $keywordType = null)
    {
        if (count($arguments) > 1) {
            throw new NodeException(sprintf(
                'Steps could have only one argument, but `%s %s` have %d.',
                $keyword,
                $text,
                count($arguments)
            ));
        }

        $this->keyword = $keyword;
        $this->text = $text;
        $this->arguments = $arguments;
        $this->line = $line;
        $this->keywordType = $keywordType ?: 'Given';
    }

    
    public function getNodeType()
    {
        return 'Step';
    }

    
    public function getType()
    {
        return $this->getKeyword();
    }

    
    public function getKeyword()
    {
        return $this->keyword;
    }

    
    public function getKeywordType()
    {
        return $this->keywordType;
    }

    
    public function getText()
    {
        return $this->text;
    }

    
    public function hasArguments()
    {
        return 0 < count($this->arguments);
    }

    
    public function getArguments()
    {
        return $this->arguments;
    }

    
    public function getLine()
    {
        return $this->line;
    }
}
