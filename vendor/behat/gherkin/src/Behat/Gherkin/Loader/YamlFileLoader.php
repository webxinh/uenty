<?php

/*
 * This file is part of the Behat Gherkin.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Gherkin\Loader;

use Behat\Gherkin\Node\FeatureNode;
use Symfony\Component\Yaml\Yaml;


class YamlFileLoader extends AbstractFileLoader
{
    private $loader;

    public function __construct()
    {
        $this->loader = new ArrayLoader();
    }

    
    public function supports($path)
    {
        return is_string($path)
            && is_file($absolute = $this->findAbsolutePath($path))
            && 'yml' === pathinfo($absolute, PATHINFO_EXTENSION);
    }

    
    public function load($path)
    {
        $path = $this->findAbsolutePath($path);
        $hash = Yaml::parse(file_get_contents($path));

        $features = $this->loader->load($hash);
        $filename = $this->findRelativePath($path);

        return array_map(function (FeatureNode $feature) use ($filename) {
            return new FeatureNode(
                $feature->getTitle(),
                $feature->getDescription(),
                $feature->getTags(),
                $feature->getBackground(),
                $feature->getScenarios(),
                $feature->getKeyword(),
                $feature->getLanguage(),
                $filename,
                $feature->getLine()
            );
        }, $features);
    }
}
