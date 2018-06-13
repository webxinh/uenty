<?php

/*
 * This file is part of the Behat Gherkin.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Gherkin\Node;


class ScenarioNode implements ScenarioInterface
{
    
    private $title;
    
    private $tags = array();
    
    private $steps = array();
    
    private $keyword;
    
    private $line;

    
    public function __construct($title, array $tags, array $steps, $keyword, $line)
    {
        $this->title = $title;
        $this->tags = $tags;
        $this->steps = $steps;
        $this->keyword = $keyword;
        $this->line = $line;
    }

    
    public function getNodeType()
    {
        return 'Scenario';
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

    
    public function getKeyword()
    {
        return $this->keyword;
    }

    
    public function getLine()
    {
        return $this->line;
    }
}
