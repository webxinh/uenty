<?php

/*
 * This file is part of the Behat Gherkin.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Gherkin\Node;


class BackgroundNode implements ScenarioLikeInterface
{
    
    private $title;
    
    private $steps = array();
    
    private $keyword;
    
    private $line;

    
    public function __construct($title, array $steps, $keyword, $line)
    {
        $this->title = $title;
        $this->steps = $steps;
        $this->keyword = $keyword;
        $this->line = $line;
    }

    
    public function getNodeType()
    {
        return 'Background';
    }

    
    public function getTitle()
    {
        return $this->title;
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
