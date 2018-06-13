<?php

/*
 * This file is part of the Behat Gherkin.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Gherkin;

use Behat\Gherkin\Filter\FeatureFilterInterface;
use Behat\Gherkin\Filter\LineFilter;
use Behat\Gherkin\Filter\LineRangeFilter;
use Behat\Gherkin\Loader\FileLoaderInterface;
use Behat\Gherkin\Loader\LoaderInterface;


class Gherkin
{
    const VERSION = '4.4-dev';

    
    protected $loaders = array();
    
    protected $filters = array();

    
    public function addLoader(LoaderInterface $loader)
    {
        $this->loaders[] = $loader;
    }

    
    public function addFilter(FeatureFilterInterface $filter)
    {
        $this->filters[] = $filter;
    }

    
    public function setFilters(array $filters)
    {
        $this->filters = array();
        array_map(array($this, 'addFilter'), $filters);
    }

    
    public function setBasePath($path)
    {
        foreach ($this->loaders as $loader) {
            if ($loader instanceof FileLoaderInterface) {
                $loader->setBasePath($path);
            }
        }
    }

    
    public function load($resource, array $filters = array())
    {
        $filters = array_merge($this->filters, $filters);

        $matches = array();
        if (preg_match('/^(.*)\:(\d+)-(\d+|\*)$/', $resource, $matches)) {
            $resource = $matches[1];
            $filters[] = new LineRangeFilter($matches[2], $matches[3]);
        } elseif (preg_match('/^(.*)\:(\d+)$/', $resource, $matches)) {
            $resource = $matches[1];
            $filters[] = new LineFilter($matches[2]);
        }

        $loader = $this->resolveLoader($resource);

        if (null === $loader) {
            return array();
        }

        $features = array();
        foreach ($loader->load($resource) as $feature) {
            foreach ($filters as $filter) {
                $feature = $filter->filterFeature($feature);

                if (!$feature->hasScenarios() && !$filter->isFeatureMatch($feature)) {
                    continue 2;
                }
            }

            $features[] = $feature;
        }

        return $features;
    }

    
    public function resolveLoader($resource)
    {
        foreach ($this->loaders as $loader) {
            if ($loader->supports($resource)) {
                return $loader;
            }
        }

        return null;
    }
}
