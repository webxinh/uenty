<?php
use Codeception\Example;

class DataProviderCest
{
     
      public function withDataProvider(ScenarioGuy $I, Example $example)
      {
          $I->amInPath($example['path']);
          $I->seeFileFound($example['file']);
      }

      
       public function withProtectedDataProvider(ScenarioGuy $I, Example $example)
       {
           $I->amInPath($example['path']);
           $I->seeFileFound($example['file']);
       }

      
       public function withDataProviderAndExample(ScenarioGuy $I, Example $example)
       {
           $I->amInPath($example['path']);
           $I->seeFileFound($example['file']);
       }

       
       public function testDependsWithDataProvider(ScenarioGuy $I, Example $example)
       {
           $I->amInPath($example['path']);
           $I->seeFileFound($example['file']);
       }

       
       public function testDependsOnTestWithDataProvider()
       {
           return true;
       }

      
      public function __exampleDataSource()
      {
          return[
              ['path' => ".", 'file' => "scenario.suite.yml"],
              ['path' => ".",  'file' => "dummy.suite.yml"],
              ['path' => ".",  'file' => "unit.suite.yml"]
          ];
      }

      
      protected function protectedDataSource()
      {
          return[
              ['path' => ".", 'file' => "scenario.suite.yml"],
              ['path' => ".",  'file' => "dummy.suite.yml"],
              ['path' => ".",  'file' => "unit.suite.yml"]
          ];
      }
}
