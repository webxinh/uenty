<?php

/*
 * This file is part of the Behat Gherkin.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Gherkin\Loader;

use Behat\Gherkin\Cache\CacheInterface;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Parser;


class GherkinFileLoader extends AbstractFileLoader
{
    protected $parser;
    protected $cache;

    
    public function __construct(Parser $parser, CacheInterface $cache = null)
    {
        $this->parser = $parser;
        $this->cache = $cache;
    }

    
    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    
    public function supports($path)
    {
        return is_string($path)
        && is_file($absolute = $this->findAbsolutePath($path))
        && 'feature' === pathinfo($absolute, PATHINFO_EXTENSION);
    }

    
    public function load($path)
    {
        $path = $this->findAbsolutePath($path);

        if ($this->cache) {
            if ($this->cache->isFresh($path, filemtime($path))) {
                $feature = $this->cache->read($path);
            } elseif (null !== $feature = $this->parseFeature($path)) {
                $this->cache->write($path, $feature);
            }
        } else {
            $feature = $this->parseFeature($path);
        }

        return null !== $feature ? array($feature) : array();
    }

    
    protected function parseFeature($path)
    {
        $filename = $this->findRelativePath($path);
        $content = file_get_contents($path);
        $feature = $this->parser->parse($content, $filename);

        return $feature;
    }
}
