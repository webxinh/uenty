<?php

/*
* This file is part of the Behat Gherkin.
* (c) Konstantin Kudryashov <ever.zet@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Behat\Gherkin\Cache;

use Behat\Gherkin\Exception\CacheException;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Gherkin;


class FileCache implements CacheInterface
{
    private $path;

    
    public function __construct($path)
    {
        $this->path = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'v'.Gherkin::VERSION;

        if (!is_dir($this->path)) {
            @mkdir($this->path, 0777, true);
        }

        if (!is_writeable($this->path)) {
            throw new CacheException(sprintf('Cache path "%s" is not writeable. Check your filesystem permissions or disable Gherkin file cache.', $this->path));
        }
    }

    
    public function isFresh($path, $timestamp)
    {
        $cachePath = $this->getCachePathFor($path);

        if (!file_exists($cachePath)) {
            return false;
        }

        return filemtime($cachePath) > $timestamp;
    }

    
    public function read($path)
    {
        $cachePath = $this->getCachePathFor($path);
        $feature = unserialize(file_get_contents($cachePath));

        if (!$feature instanceof FeatureNode) {
            throw new CacheException(sprintf('Can not load cache for a feature "%s" from "%s".', $path, $cachePath ));
        }

        return $feature;
    }

    
    public function write($path, FeatureNode $feature)
    {
        file_put_contents($this->getCachePathFor($path), serialize($feature));
    }

    
    protected function getCachePathFor($path)
    {
        return $this->path.'/'.md5($path).'.feature.cache';
    }
}
