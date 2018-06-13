<?php

/*
 * This file is part of the Behat Gherkin.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Gherkin\Keywords;


class ArrayKeywords implements KeywordsInterface
{
    private $keywords = array();
    private $keywordString = array();
    private $language;

    
    public function __construct(array $keywords)
    {
        $this->keywords = $keywords;
    }

    
    public function setLanguage($language)
    {
        if (!isset($this->keywords[$language])) {
            $this->language = 'en';
        } else {
            $this->language = $language;
        }
    }

    
    public function getFeatureKeywords()
    {
        return $this->keywords[$this->language]['feature'];
    }

    
    public function getBackgroundKeywords()
    {
        return $this->keywords[$this->language]['background'];
    }

    
    public function getScenarioKeywords()
    {
        return $this->keywords[$this->language]['scenario'];
    }

    
    public function getOutlineKeywords()
    {
        return $this->keywords[$this->language]['scenario_outline'];
    }

    
    public function getExamplesKeywords()
    {
        return $this->keywords[$this->language]['examples'];
    }

    
    public function getGivenKeywords()
    {
        return $this->keywords[$this->language]['given'];
    }

    
    public function getWhenKeywords()
    {
        return $this->keywords[$this->language]['when'];
    }

    
    public function getThenKeywords()
    {
        return $this->keywords[$this->language]['then'];
    }

    
    public function getAndKeywords()
    {
        return $this->keywords[$this->language]['and'];
    }

    
    public function getButKeywords()
    {
        return $this->keywords[$this->language]['but'];
    }

    
    public function getStepKeywords()
    {
        if (!isset($this->keywordString[$this->language])) {
            $keywords = array_merge(
                explode('|', $this->getGivenKeywords()),
                explode('|', $this->getWhenKeywords()),
                explode('|', $this->getThenKeywords()),
                explode('|', $this->getAndKeywords()),
                explode('|', $this->getButKeywords())
            );

            usort($keywords, function ($keyword1, $keyword2) {
                return mb_strlen($keyword2, 'utf8') - mb_strlen($keyword1, 'utf8');
            });

            $this->keywordString[$this->language] = implode('|', $keywords);
        }

        return $this->keywordString[$this->language];
    }
}
