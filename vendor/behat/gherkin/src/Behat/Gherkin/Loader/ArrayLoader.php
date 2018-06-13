<?php

/*
 * This file is part of the Behat Gherkin.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Gherkin\Loader;

use Behat\Gherkin\Node\BackgroundNode;
use Behat\Gherkin\Node\ExampleTableNode;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\ScenarioNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Gherkin\Node\TableNode;


class ArrayLoader implements LoaderInterface
{
    
    public function supports($resource)
    {
        return is_array($resource) && (isset($resource['features']) || isset($resource['feature']));
    }

    
    public function load($resource)
    {
        $features = array();

        if (isset($resource['features'])) {
            foreach ($resource['features'] as $iterator => $hash) {
                $feature = $this->loadFeatureHash($hash, $iterator);
                $features[] = $feature;
            }
        } elseif (isset($resource['feature'])) {
            $feature = $this->loadFeatureHash($resource['feature']);
            $features[] = $feature;
        }

        return $features;
    }

    
    protected function loadFeatureHash(array $hash, $line = 0)
    {
        $hash = array_merge(
            array(
                'title' => null,
                'description' => null,
                'tags' => array(),
                'keyword' => 'Feature',
                'language' => 'en',
                'line' => $line,
                'scenarios' => array(),
            ),
            $hash
        );
        $background = isset($hash['background']) ? $this->loadBackgroundHash($hash['background']) : null;

        $scenarios = array();
        foreach ((array) $hash['scenarios'] as $scenarioIterator => $scenarioHash) {
            if (isset($scenarioHash['type']) && 'outline' === $scenarioHash['type']) {
                $scenarios[] = $this->loadOutlineHash($scenarioHash, $scenarioIterator);
            } else {
                $scenarios[] = $this->loadScenarioHash($scenarioHash, $scenarioIterator);
            }
        }

        return new FeatureNode($hash['title'], $hash['description'], $hash['tags'], $background, $scenarios, $hash['keyword'], $hash['language'], null, $hash['line']);
    }

    
    protected function loadBackgroundHash(array $hash)
    {
        $hash = array_merge(
            array(
                'title' => null,
                'keyword' => 'Background',
                'line' => 0,
                'steps' => array(),
            ),
            $hash
        );

        $steps = $this->loadStepsHash($hash['steps']);

        return new BackgroundNode($hash['title'], $steps, $hash['keyword'], $hash['line']);
    }

    
    protected function loadScenarioHash(array $hash, $line = 0)
    {
        $hash = array_merge(
            array(
                'title' => null,
                'tags' => array(),
                'keyword' => 'Scenario',
                'line' => $line,
                'steps' => array(),
            ),
            $hash
        );

        $steps = $this->loadStepsHash($hash['steps']);

        return new ScenarioNode($hash['title'], $hash['tags'], $steps, $hash['keyword'], $hash['line']);
    }

    
    protected function loadOutlineHash(array $hash, $line = 0)
    {
        $hash = array_merge(
            array(
                'title' => null,
                'tags' => array(),
                'keyword' => 'Scenario Outline',
                'line' => $line,
                'steps' => array(),
                'examples' => array(),
            ),
            $hash
        );

        $steps = $this->loadStepsHash($hash['steps']);

        if (isset($hash['examples']['keyword'])) {
            $examplesKeyword = $hash['examples']['keyword'];
            unset($hash['examples']['keyword']);
        } else {
            $examplesKeyword = 'Examples';
        }

        $examples = new ExampleTableNode($hash['examples'], $examplesKeyword);

        return new OutlineNode($hash['title'], $hash['tags'], $steps, $examples, $hash['keyword'], $hash['line']);
    }

    
    private function loadStepsHash(array $hash)
    {
        $steps = array();
        foreach ($hash as $stepIterator => $stepHash) {
            $steps[] = $this->loadStepHash($stepHash, $stepIterator);
        }

        return $steps;
    }

    
    protected function loadStepHash(array $hash, $line = 0)
    {
        $hash = array_merge(
            array(
                'keyword_type' => 'Given',
                'type' => 'Given',
                'text' => null,
                'keyword' => 'Scenario',
                'line' => $line,
                'arguments' => array(),
            ),
            $hash
        );

        $arguments = array();
        foreach ($hash['arguments'] as $argumentHash) {
            if ('table' === $argumentHash['type']) {
                $arguments[] = $this->loadTableHash($argumentHash['rows']);
            } elseif ('pystring' === $argumentHash['type']) {
                $arguments[] = $this->loadPyStringHash($argumentHash, $hash['line'] + 1);
            }
        }

        return new StepNode($hash['type'], $hash['text'], $arguments, $hash['line'], $hash['keyword_type']);
    }

    
    protected function loadTableHash(array $hash)
    {
        return new TableNode($hash);
    }

    
    protected function loadPyStringHash(array $hash, $line = 0)
    {
        $line = isset($hash['line']) ? $hash['line'] : $line;

        $strings = array();
        foreach (explode("\n", $hash['text']) as $string) {
            $strings[] = $string;
        }

        return new PyStringNode($strings, $line);
    }
}
