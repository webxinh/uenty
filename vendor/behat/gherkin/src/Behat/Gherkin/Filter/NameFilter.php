<?php

/*
 * This file is part of the Behat Gherkin.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Gherkin\Filter;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioInterface;


class NameFilter extends SimpleFilter
{
    protected $filterString;

    
    public function __construct($filterString)
    {
        $this->filterString = trim($filterString);
    }

    
    public function isFeatureMatch(FeatureNode $feature)
    {
        if ('/' === $this->filterString[0]) {
            return 1 === preg_match($this->filterString, $feature->getTitle());
        }

        return false !== mb_strpos($feature->getTitle(), $this->filterString, 0, 'utf8');
    }

    
    public function isScenarioMatch(ScenarioInterface $scenario)
    {
        if ('/' === $this->filterString[0] && 1 === preg_match($this->filterString, $scenario->getTitle())) {
            return true;
        } elseif (false !== mb_strpos($scenario->getTitle(), $this->filterString, 0, 'utf8')) {
            return true;
        }

        return false;
    }
}
