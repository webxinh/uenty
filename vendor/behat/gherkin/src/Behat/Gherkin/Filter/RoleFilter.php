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


class RoleFilter extends SimpleFilter
{
    protected $pattern;

    
    public function __construct($role)
    {
        $this->pattern = '/as an? ' . strtr(preg_quote($role, '/'), array(
            '\*' => '.*',
            '\?' => '.',
            '\[' => '[',
            '\]' => ']'
        )) . '[$\n]/i';
    }

    
    public function isFeatureMatch(FeatureNode $feature)
    {
        return 1 === preg_match($this->pattern, $feature->getDescription());
    }

    
    public function isScenarioMatch(ScenarioInterface $scenario)
    {
        return false;
    }
}
