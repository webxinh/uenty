<?php

/*
 * This file is part of the Behat Gherkin.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Gherkin\Node;


class ExampleNode implements ScenarioInterface
{
    
    private $title;
    
    private $tags;
    
    private $outlineSteps;
    
    private $tokens;
    
    private $line;
    
    private $steps;

    
    public function __construct($title, array $tags, $outlineSteps, array $tokens, $line)
    {
        $this->title = $title;
        $this->tags = $tags;
        $this->outlineSteps = $outlineSteps;
        $this->tokens = $tokens;
        $this->line = $line;
    }

    
    public function getNodeType()
    {
        return 'Example';
    }

    
    public function getKeyword()
    {
        return $this->getNodeType();
    }

    
    public function getTitle()
    {
        return $this->title;
    }

    
    public function hasTag($tag)
    {
        return in_array($tag, $this->getTags());
    }

    
    public function hasTags()
    {
        return 0 < count($this->getTags());
    }

    
    public function getTags()
    {
        return $this->tags;
    }

    
    public function hasSteps()
    {
        return 0 < count($this->outlineSteps);
    }

    
    public function getSteps()
    {
        return $this->steps = $this->steps ? : $this->createExampleSteps();
    }

    
    public function getTokens()
    {
        return $this->tokens;
    }

    
    public function getLine()
    {
        return $this->line;
    }

    
    protected function createExampleSteps()
    {
        $steps = array();
        foreach ($this->outlineSteps as $outlineStep) {
            $keyword = $outlineStep->getKeyword();
            $keywordType = $outlineStep->getKeywordType();
            $text = $this->replaceTextTokens($outlineStep->getText());
            $args = $this->replaceArgumentsTokens($outlineStep->getArguments());
            $line = $outlineStep->getLine();

            $steps[] = new StepNode($keyword, $text, $args, $line, $keywordType);
        }

        return $steps;
    }

    
    protected function replaceArgumentsTokens(array $arguments)
    {
        foreach ($arguments as $num => $argument) {
            if ($argument instanceof TableNode) {
                $arguments[$num] = $this->replaceTableArgumentTokens($argument);
            }
            if ($argument instanceof PyStringNode) {
                $arguments[$num] = $this->replacePyStringArgumentTokens($argument);
            }
        }

        return $arguments;
    }

    
    protected function replaceTableArgumentTokens(TableNode $argument)
    {
        $table = $argument->getTable();
        foreach ($table as $line => $row) {
            foreach (array_keys($row) as $col) {
                $table[$line][$col] = $this->replaceTextTokens($table[$line][$col]);
            }
        }

        return new TableNode($table);
    }

    
    protected function replacePyStringArgumentTokens(PyStringNode $argument)
    {
        $strings = $argument->getStrings();
        foreach ($strings as $line => $string) {
            $strings[$line] = $this->replaceTextTokens($strings[$line]);
        }

        return new PyStringNode($strings, $argument->getLine());
    }

    
    protected function replaceTextTokens($text)
    {
        foreach ($this->tokens as $key => $val) {
            $text = str_replace('<' . $key . '>', $val, $text);
        }

        return $text;
    }
}
