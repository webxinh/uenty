<?php

/*
 * This file is part of the Behat Gherkin.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Gherkin\Keywords;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;


class CucumberKeywords extends ArrayKeywords
{
    
    public function __construct($yaml)
    {
        // Handle filename explicitly for BC reasons, as Symfony Yaml 3.0 does not do it anymore
        $file = null;
        if (strpos($yaml, "\n") === false && is_file($yaml)) {
            if (false === is_readable($yaml)) {
                throw new ParseException(sprintf('Unable to parse "%s" as the file is not readable.', $yaml));
            }

            $file = $yaml;
            $yaml = file_get_contents($file);
        }

        try {
            $content = Yaml::parse($yaml);
        } catch (ParseException $e) {
            if ($file) {
                $e->setParsedFile($file);
            }

            throw $e;
        }

        parent::__construct($content);
    }

    
    public function getGivenKeywords()
    {
        return $this->prepareStepString(parent::getGivenKeywords());
    }

    
    public function getWhenKeywords()
    {
        return $this->prepareStepString(parent::getWhenKeywords());
    }

    
    public function getThenKeywords()
    {
        return $this->prepareStepString(parent::getThenKeywords());
    }

    
    public function getAndKeywords()
    {
        return $this->prepareStepString(parent::getAndKeywords());
    }

    
    public function getButKeywords()
    {
        return $this->prepareStepString(parent::getButKeywords());
    }

    
    private function prepareStepString($keywordsString)
    {
        if (0 === mb_strpos($keywordsString, '*|', 0, 'UTF-8')) {
            $keywordsString = mb_substr($keywordsString, 2, mb_strlen($keywordsString, 'utf8') - 2, 'utf8');
        }

        return $keywordsString;
    }
}
