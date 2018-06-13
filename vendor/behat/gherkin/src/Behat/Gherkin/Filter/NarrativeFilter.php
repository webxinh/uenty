<?php

/*
 * This file is part of the Gherkin.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Gherkin\Filter;

use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Gherkin\Node\FeatureNode;


class NarrativeFilter extends SimpleFilter
{
    
    private $regex;

    
    public function __construct($regex)
    {
        $this->regex = $regex;
    }

    
    public function isFeatureMatch(FeatureNode $feature)
    {
        return 1 === preg_match($this->regex, $feature->getDescription());
    }

    
    public function isScenarioMatch(ScenarioInterface $scenario)
    {
        return false;
    }
}
