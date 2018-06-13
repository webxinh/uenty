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


class PathsFilter extends SimpleFilter
{
    protected $filterPaths = array();

    
    public function __construct(array $paths)
    {
        $this->filterPaths = array_map(
            function ($realpath) {
                return rtrim($realpath, DIRECTORY_SEPARATOR) .
                    (is_dir($realpath) ? DIRECTORY_SEPARATOR : '');
            },
            array_filter(
                array_map('realpath', $paths)
            )
        );
    }

    
    public function isFeatureMatch(FeatureNode $feature)
    {
        foreach ($this->filterPaths as $path) {
            if (0 === strpos($feature->getFile(), $path)) {
                return true;
            }
        }

        return false;
    }

    
    public function isScenarioMatch(ScenarioInterface $scenario)
    {
        return false;
    }
}
