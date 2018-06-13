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


interface FeatureFilterInterface
{
    
    public function isFeatureMatch(FeatureNode $feature);

    
    public function filterFeature(FeatureNode $feature);
}
