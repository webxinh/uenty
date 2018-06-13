<?php

/*
 * This file is part of the Behat Gherkin.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Gherkin\Cache;

use Behat\Gherkin\Node\FeatureNode;


interface CacheInterface
{
    
    public function isFresh($path, $timestamp);

    
    public function read($path);

    
    public function write($path, FeatureNode $feature);
}
