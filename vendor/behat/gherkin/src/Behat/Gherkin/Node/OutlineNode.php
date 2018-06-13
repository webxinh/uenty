<?php

/*
 * This file is part of the Behat Gherkin.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Gherkin\Node;


class OutlineNode implements ScenarioInterface
{
    
    private $title;
    
    private $tags;
    
    private $steps;
    
    private $table;
    
    private $keyword;
    
    private $line;
    
    private $examples;

    
    public function __construct(
        $title,
        array $tags,
        array $steps,
        ExampleTableNode $table,
        $keyword,
        $line
    ) {
        $this->title = $title;
        $this->tags = $tags;
        $this->steps = $steps;
        $this->table = $table;
        $this->keyword = $keyword;
        $this->line = $line;
    }

    
    public function getNodeType()
    {
        return 'Outline';
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
        return 0 < count($this->steps);
    }

    
    public function getSteps()
    {
        return $this->steps;
    }

    
    public function hasExamples()
    {
        return 0 < count($this->table->getColumnsHash());
    }

    
    public function getExampleTable()
    {
        return $this->table;
    }

    
    public function getExamples()
    {
        return $this->examples = $this->examples ? : $this->createExamples();
    }

    
    public function getKeyword()
    {
        return $this->keyword;
    }

    
    public function getLine()
    {
        return $this->line;
    }

    
    protected function createExamples()
    {
        $examples = array();
        foreach ($this->table->getColumnsHash() as $rowNum => $row) {
            $examples[] = new ExampleNode(
                $this->table->getRowAsString($rowNum + 1),
                $this->tags,
                $this->getSteps(),
                $row,
                $this->table->getRowLine($rowNum + 1)
            );
        }

        return $examples;
    }
}
