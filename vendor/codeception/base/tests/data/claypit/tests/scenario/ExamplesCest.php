<?php
use Codeception\Example;

class ExamplesCest
{
    
    public function filesExistsAnnotation(ScenarioGuy $I, Example $example)
    {
        $I->amInPath($example['path']);
        $I->seeFileFound($example['file']);
    }


    
    public function filesExistsByJson(ScenarioGuy $I, Example $example)
    {
        $I->amInPath($example['path']);
        $I->seeFileFound($example['file']);
    }

    
    public function filesExistsByArray(ScenarioGuy $I, Example $example)
    {
        $I->amInPath($example[0]);
        $I->seeFileFound($example[1]);
    }

    
    public function filesExistsComplexJson(ScenarioGuy $I, Example $examples)
    {
        foreach ($examples as $example) {
            $I->amInPath($example['path']);
            $I->seeFileFound($example['file']);
        }
    }

}