<?php

/*
 * This file is part of the Behat Gherkin.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Gherkin\Node;


class FeatureNode implements KeywordNodeInterface, TaggedNodeInterface
{
    
    private $title;
    
    private $description;
    
    private $tags = array();
    
    private $background;
    
    private $scenarios = array();
    
    private $keyword;
    
    private $language;
    
    private $file;
    
    private $line;

    
    public function __construct(
        $title,
        $description,
        array $tags,
        BackgroundNode $background = null,
        array $scenarios,
        $keyword,
        $language,
        $file,
        $line
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->tags = $tags;
        $this->background = $background;
        $this->scenarios = $scenarios;
        $this->keyword = $keyword;
        $this->language = $language;
        $this->file = $file;
        $this->line = $line;
    }

    
    public function getNodeType()
    {
        return 'Feature';
    }

    
    public function getTitle()
    {
        return $this->title;
    }

    
    public function hasDescription()
    {
        return !empty($this->description);
    }

    
    public function getDescription()
    {
        return $this->description;
    }

    
    public function hasTag($tag)
    {
        return in_array($tag, $this->tags);
    }

    
    public function hasTags()
    {
        return 0 < count($this->tags);
    }

    
    public function getTags()
    {
        return $this->tags;
    }

    
    public function hasBackground()
    {
        return null !== $this->background;
    }

    
    public function getBackground()
    {
        return $this->background;
    }

    
    public function hasScenarios()
    {
        return 0 < count($this->scenarios);
    }

    
    public function getScenarios()
    {
        return $this->scenarios;
    }

    
    public function getKeyword()
    {
        return $this->keyword;
    }

    
    public function getLanguage()
    {
        return $this->language;
    }

    
    public function getFile()
    {
        return $this->file;
    }

    
    public function getLine()
    {
        return $this->line;
    }
}
