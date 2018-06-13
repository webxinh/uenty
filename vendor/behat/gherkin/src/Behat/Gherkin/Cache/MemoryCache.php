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


class MemoryCache implements CacheInterface
{
    private $features = array();
    private $timestamps = array();

    
    public function isFresh($path, $timestamp)
    {
        if (!isset($this->features[$path])) {
            return false;
        }

        return $this->timestamps[$path] > $timestamp;
    }

    
    public function read($path)
    {
        return $this->features[$path];
    }

    
    public function write($path, FeatureNode $feature)
    {
        $this->features[$path]   = $feature;
        $this->timestamps[$path] = time();
    }
}
