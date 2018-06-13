<?php

/*
 * This file is part of the Behat Gherkin.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Gherkin\Loader;

use Behat\Gherkin\Gherkin;
use Behat\Gherkin\Node\FeatureNode;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;


class DirectoryLoader extends AbstractFileLoader
{
    protected $gherkin;

    
    public function __construct(Gherkin $gherkin)
    {
        $this->gherkin = $gherkin;
    }

    
    public function supports($path)
    {
        return is_string($path)
        && is_dir($this->findAbsolutePath($path));
    }

    
    public function load($path)
    {
        $path = $this->findAbsolutePath($path);

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        $paths = array_map('strval', iterator_to_array($iterator));
        uasort($paths, 'strnatcasecmp');

        $features = array();

        foreach ($paths as $path) {
            $path = (string) $path;
            $loader = $this->gherkin->resolveLoader($path);

            if (null !== $loader) {
                $features = array_merge($features, $loader->load($path));
            }
        }

        return $features;
    }
}
