<?php


namespace aabc\console\controllers;

use Aabc;
use aabc\base\InvalidConfigException;
use aabc\base\InvalidParamException;
use aabc\console\Controller;
use aabc\console\Exception;
use aabc\helpers\Console;
use aabc\helpers\FileHelper;
use aabc\test\FixtureTrait;


class FixtureController extends Controller
{
    use FixtureTrait;

    
    public $defaultAction = 'load';
    
    public $namespace = 'tests\unit\fixtures';
    
    public $globalFixtures = [
        'aabc\test\InitDb',
    ];


    
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), [
            'namespace', 'globalFixtures'
        ]);
    }

    
    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), [
            'g' => 'globalFixtures',
            'n' => 'namespace',
        ]);
    }

    
    public function actionLoad(array $fixturesInput = [])
    {
        if ($fixturesInput === []) {
            $this->stdout($this->getHelpSummary() . "\n");

            $helpCommand = Console::ansiFormat('aabc help fixture', [Console::FG_CYAN]);
            $this->stdout("Use $helpCommand to get usage info.\n");

            return self::EXIT_CODE_NORMAL;
        }

        $filtered = $this->filterFixtures($fixturesInput);
        $except = $filtered['except'];

        if (!$this->needToApplyAll($fixturesInput[0])) {
            $fixtures = $filtered['apply'];

            $foundFixtures = $this->findFixtures($fixtures);
            $notFoundFixtures = array_diff($fixtures, $foundFixtures);

            if ($notFoundFixtures) {
                $this->notifyNotFound($notFoundFixtures);
            }
        } else {
            $foundFixtures = $this->findFixtures();
        }

        $fixturesToLoad = array_diff($foundFixtures, $except);

        if (!$foundFixtures) {
            throw new Exception(
                "No files were found for: \"" . implode(', ', $fixturesInput) . "\".\n" .
                "Check that files exist under fixtures path: \n\"" . $this->getFixturePath() . "\"."
            );
        }

        if (!$fixturesToLoad) {
            $this->notifyNothingToLoad($foundFixtures, $except);
            return static::EXIT_CODE_NORMAL;
        }

        if (!$this->confirmLoad($fixturesToLoad, $except)) {
            return static::EXIT_CODE_NORMAL;
        }

        $fixtures = $this->getFixturesConfig(array_merge($this->globalFixtures, $fixturesToLoad));

        if (!$fixtures) {
            throw new Exception('No fixtures were found in namespace: "' . $this->namespace . '"' . '');
        }

        $fixturesObjects = $this->createFixtures($fixtures);

        $this->unloadFixtures($fixturesObjects);
        $this->loadFixtures($fixturesObjects);
        $this->notifyLoaded($fixtures);

        return static::EXIT_CODE_NORMAL;
    }

    
    public function actionUnload(array $fixturesInput = [])
    {
        $filtered = $this->filterFixtures($fixturesInput);
        $except = $filtered['except'];

        if (!$this->needToApplyAll($fixturesInput[0])) {
            $fixtures = $filtered['apply'];

            $foundFixtures = $this->findFixtures($fixtures);
            $notFoundFixtures = array_diff($fixtures, $foundFixtures);

            if ($notFoundFixtures) {
                $this->notifyNotFound($notFoundFixtures);
            }
        } else {
            $foundFixtures = $this->findFixtures();
        }

        $fixturesToUnload = array_diff($foundFixtures, $except);

        if (!$foundFixtures) {
            throw new Exception(
                "No files were found for: \"" . implode(', ', $fixturesInput) . "\".\n" .
                "Check that files exist under fixtures path: \n\"" . $this->getFixturePath() . "\"."
            );
        }

        if (!$fixturesToUnload) {
            $this->notifyNothingToUnload($foundFixtures, $except);
            return static::EXIT_CODE_NORMAL;
        }

        if (!$this->confirmUnload($fixturesToUnload, $except)) {
            return static::EXIT_CODE_NORMAL;
        }

        $fixtures = $this->getFixturesConfig(array_merge($this->globalFixtures, $fixturesToUnload));

        if (!$fixtures) {
            throw new Exception('No fixtures were found in namespace: ' . $this->namespace . '".');
        }

        $this->unloadFixtures($this->createFixtures($fixtures));
        $this->notifyUnloaded($fixtures);
    }

    
    private function notifyLoaded($fixtures)
    {
        $this->stdout("Fixtures were successfully loaded from namespace:\n", Console::FG_YELLOW);
        $this->stdout("\t\"" . Aabc::getAlias($this->namespace) . "\"\n\n", Console::FG_GREEN);
        $this->outputList($fixtures);
    }

    
    public function notifyNothingToLoad($foundFixtures, $except)
    {
        $this->stdout("Fixtures to load could not be found according given conditions:\n\n", Console::FG_RED);
        $this->stdout("Fixtures namespace is: \n", Console::FG_YELLOW);
        $this->stdout("\t" . $this->namespace . "\n", Console::FG_GREEN);

        if (count($foundFixtures)) {
            $this->stdout("\nFixtures founded under the namespace:\n\n", Console::FG_YELLOW);
            $this->outputList($foundFixtures);
        }

        if (count($except)) {
            $this->stdout("\nFixtures that will NOT be loaded: \n\n", Console::FG_YELLOW);
            $this->outputList($except);
        }
    }

    
    public function notifyNothingToUnload($foundFixtures, $except)
    {
        $this->stdout("Fixtures to unload could not be found according to given conditions:\n\n", Console::FG_RED);
        $this->stdout("Fixtures namespace is: \n", Console::FG_YELLOW);
        $this->stdout("\t" . $this->namespace . "\n", Console::FG_GREEN);

        if (count($foundFixtures)) {
            $this->stdout("\nFixtures found under the namespace:\n\n", Console::FG_YELLOW);
            $this->outputList($foundFixtures);
        }

        if (count($except)) {
            $this->stdout("\nFixtures that will NOT be unloaded: \n\n", Console::FG_YELLOW);
            $this->outputList($except);
        }
    }

    
    private function notifyUnloaded($fixtures)
    {
        $this->stdout("\nFixtures were successfully unloaded from namespace: ", Console::FG_YELLOW);
        $this->stdout(Aabc::getAlias($this->namespace) . "\"\n\n", Console::FG_GREEN);
        $this->outputList($fixtures);
    }

    
    private function notifyNotFound($fixtures)
    {
        $this->stdout("Some fixtures were not found under path:\n", Console::BG_RED);
        $this->stdout("\t" . $this->getFixturePath() . "\n\n", Console::FG_GREEN);
        $this->stdout("Check that they have correct namespace \"{$this->namespace}\" \n", Console::BG_RED);
        $this->outputList($fixtures);
        $this->stdout("\n");
    }

    
    private function confirmLoad($fixtures, $except)
    {
        $this->stdout("Fixtures namespace is: \n", Console::FG_YELLOW);
        $this->stdout("\t" . $this->namespace . "\n\n", Console::FG_GREEN);

        if (count($this->globalFixtures)) {
            $this->stdout("Global fixtures will be used:\n\n", Console::FG_YELLOW);
            $this->outputList($this->globalFixtures);
        }

        if (count($fixtures)) {
            $this->stdout("\nFixtures below will be loaded:\n\n", Console::FG_YELLOW);
            $this->outputList($fixtures);
        }

        if (count($except)) {
            $this->stdout("\nFixtures that will NOT be loaded: \n\n", Console::FG_YELLOW);
            $this->outputList($except);
        }

        $this->stdout("\nBe aware that:\n", Console::BOLD);
        $this->stdout("Applying leads to purging of certain data in the database!\n", Console::FG_RED);

        return $this->confirm("\nLoad above fixtures?");
    }

    
    private function confirmUnload($fixtures, $except)
    {
        $this->stdout("Fixtures namespace is: \n", Console::FG_YELLOW);
        $this->stdout("\t" . $this->namespace . "\n\n", Console::FG_GREEN);

        if (count($this->globalFixtures)) {
            $this->stdout("Global fixtures will be used:\n\n", Console::FG_YELLOW);
            $this->outputList($this->globalFixtures);
        }

        if (count($fixtures)) {
            $this->stdout("\nFixtures below will be unloaded:\n\n", Console::FG_YELLOW);
            $this->outputList($fixtures);
        }

        if (count($except)) {
            $this->stdout("\nFixtures that will NOT be unloaded:\n\n", Console::FG_YELLOW);
            $this->outputList($except);
        }

        return $this->confirm("\nUnload fixtures?");
    }

    
    private function outputList($data)
    {
        foreach ($data as $index => $item) {
            $this->stdout("\t" . ($index + 1) . ". {$item}\n", Console::FG_GREEN);
        }
    }

    
    public function needToApplyAll($fixture)
    {
        return $fixture === '*';
    }

    
    private function findFixtures(array $fixtures = [])
    {
        $fixturesPath = $this->getFixturePath();

        $filesToSearch = ['*Fixture.php'];
        $findAll = ($fixtures === []);

        if (!$findAll) {
            $filesToSearch = [];

            foreach ($fixtures as $fileName) {
                $filesToSearch[] = $fileName . 'Fixture.php';
            }
        }

        $files = FileHelper::findFiles($fixturesPath, ['only' => $filesToSearch]);
        $foundFixtures = [];

        foreach ($files as $fixture) {
            $foundFixtures[] = basename($fixture, 'Fixture.php');
        }

        return $foundFixtures;
    }

    
    private function getFixturesConfig($fixtures)
    {
        $config = [];

        foreach ($fixtures as $fixture) {
            $isNamespaced = (strpos($fixture, '\\') !== false);
            $fullClassName = $isNamespaced ? $fixture . 'Fixture' : $this->namespace . '\\' . $fixture . 'Fixture';

            if (class_exists($fullClassName)) {
                $config[] = $fullClassName;
            }
        }

        return $config;
    }

    
    private function filterFixtures($fixtures)
    {
        $filtered = [
            'apply' => [],
            'except' => [],
        ];

        foreach ($fixtures as $fixture) {
            if (mb_strpos($fixture, '-') !== false) {
                $filtered['except'][] = str_replace('-', '', $fixture);
            } else {
                $filtered['apply'][] = $fixture;
            }
        }

        return $filtered;
    }

    
    private function getFixturePath()
    {
        try {
            return Aabc::getAlias('@' . str_replace('\\', '/', $this->namespace));
        } catch (InvalidParamException $e) {
            throw new InvalidConfigException('Invalid fixture namespace: "' . $this->namespace . '". Please, check your FixtureController::namespace parameter');
        }
    }
}
