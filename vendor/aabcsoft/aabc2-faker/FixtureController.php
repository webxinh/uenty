<?php


namespace aabc\faker;

use Aabc;
use aabc\console\Exception;
use aabc\helpers\Console;
use aabc\helpers\FileHelper;
use aabc\helpers\VarDumper;


class FixtureController extends \aabc\console\controllers\FixtureController
{
    
    public $templatePath = '@tests/unit/templates/fixtures';
    
    public $fixtureDataPath = '@tests/unit/fixtures/data';
    
    public $language;
    
    public $count = 2;
    
    public $providers = [];

    
    private $_generator;


    
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), [
            'templatePath', 'language', 'fixtureDataPath', 'count'
        ]);
    }

    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $this->checkPaths();
            $this->addProviders();
            return true;
        } else {
            return false;
        }
    }

    
    public function actionTemplates()
    {
        $foundTemplates = $this->findTemplatesFiles();

        if (!$foundTemplates) {
            $this->notifyNoTemplatesFound();
        } else {
            $this->notifyTemplatesCanBeGenerated($foundTemplates);
        }
    }

    
    public function actionGenerate()
    {
        $templatesInput = func_get_args();

        if (empty($templatesInput)) {
            throw new Exception('You should specify input fixtures template files');
        }

        $foundTemplates = $this->findTemplatesFiles($templatesInput);

        $notFoundTemplates = array_diff($templatesInput, $foundTemplates);

        if ($notFoundTemplates) {
            $this->notifyNotFoundTemplates($notFoundTemplates);
        }

        if (!$foundTemplates) {
            $this->notifyNoTemplatesFound();
            return static::EXIT_CODE_NORMAL;
        }

        if (!$this->confirmGeneration($foundTemplates)) {
            return static::EXIT_CODE_NORMAL;
        }

        $templatePath = Aabc::getAlias($this->templatePath);
        $fixtureDataPath = Aabc::getAlias($this->fixtureDataPath);

        FileHelper::createDirectory($fixtureDataPath);

        $generatedTemplates = [];

        foreach ($foundTemplates as $templateName) {
            $this->generateFixtureFile($templateName, $templatePath, $fixtureDataPath);
            $generatedTemplates[] = $templateName;
        }

        $this->notifyTemplatesGenerated($generatedTemplates);
    }

    
    public function actionGenerateAll()
    {
        $foundTemplates = $this->findTemplatesFiles();

        if (!$foundTemplates) {
            $this->notifyNoTemplatesFound();
            return static::EXIT_CODE_NORMAL;
        }

        if (!$this->confirmGeneration($foundTemplates)) {
            return static::EXIT_CODE_NORMAL;
        }

        $templatePath = Aabc::getAlias($this->templatePath);
        $fixtureDataPath = Aabc::getAlias($this->fixtureDataPath);

        FileHelper::createDirectory($fixtureDataPath);

        $generatedTemplates = [];

        foreach ($foundTemplates as $templateName) {
            $this->generateFixtureFile($templateName, $templatePath, $fixtureDataPath);            
            $generatedTemplates[] = $templateName;
        }

        $this->notifyTemplatesGenerated($generatedTemplates);
    }

    
    private function notifyNotFoundTemplates($templatesNames)
    {
        $this->stdout("The following fixtures templates were NOT found:\n\n", Console::FG_RED);

        foreach ($templatesNames as $name) {
            $this->stdout("\t * $name \n", Console::FG_GREEN);
        }

        $this->stdout("\n");
    }

    
    private function notifyNoTemplatesFound()
    {
        $this->stdout("No fixtures template files matching input conditions were found under the path:\n\n", Console::FG_RED);
        $this->stdout("\t " . Aabc::getAlias($this->templatePath) . " \n\n", Console::FG_GREEN);
    }

    
    private function notifyTemplatesGenerated($templatesNames)
    {
        $this->stdout("The following fixtures template files were generated:\n\n", Console::FG_YELLOW);

        foreach ($templatesNames as $name) {
            $this->stdout("\t* " . $name . "\n", Console::FG_GREEN);
        }

        $this->stdout("\n");
    }

    private function notifyTemplatesCanBeGenerated($templatesNames)
    {
        $this->stdout("Template files path: ", Console::FG_YELLOW);
        $this->stdout(Aabc::getAlias($this->templatePath) . "\n\n", Console::FG_GREEN);

        foreach ($templatesNames as $name) {
            $this->stdout("\t* " . $name . "\n", Console::FG_GREEN);
        }

        $this->stdout("\n");
    }

    
    private function findTemplatesFiles(array $templatesNames = [])
    {
        $findAll = ($templatesNames == []);

        if ($findAll) {
            $files = FileHelper::findFiles(Aabc::getAlias($this->templatePath), ['only' => ['*.php']]);
        } else {
            $filesToSearch = [];

            foreach ($templatesNames as $fileName) {
                $filesToSearch[] = $fileName . '.php';
            }

            $files = FileHelper::findFiles(Aabc::getAlias($this->templatePath), ['only' => $filesToSearch]);
        }

        $foundTemplates = [];

        foreach ($files as $fileName) {
            $foundTemplates[] = basename($fileName, '.php');
        }

        return $foundTemplates;
    }

    
    public function getGenerator()
    {
        if ($this->_generator === null) {
            $language = $this->language === null ? Aabc::$app->language : $this->language;
            $this->_generator = \Faker\Factory::create(str_replace('-', '_', $language));
        }
        return $this->_generator;
    }

    
    public function checkPaths()
    {
        $path = Aabc::getAlias($this->templatePath, false);

        if (!$path || !is_dir($path)) {
            throw new Exception("The template path \"{$this->templatePath}\" does not exist");
        }
    }

    
    public function addProviders()
    {
        foreach ($this->providers as $provider) {
            $this->generator->addProvider(new $provider($this->generator));
        }
    }

    
    public function exportFixtures($fixtures)
    {
        return "<?php\n\nreturn " . VarDumper::export($fixtures) . ";\n";
    }

    
    public function generateFixture($_template_, $index)
    {
        // $faker and $index are exposed to the template file
        $faker = $this->getGenerator();
        return require($_template_);
    }

    
    public function generateFixtureFile($templateName, $templatePath, $fixtureDataPath)
    {
        $fixtures = [];

        for ($i = 0; $i < $this->count; $i++) {
            $fixtures[$i] = $this->generateFixture($templatePath . '/' . $templateName . '.php', $i);
        }

        $content = $this->exportFixtures($fixtures);

        file_put_contents($fixtureDataPath . '/'. $templateName . '.php', $content);
    }

    
    public function confirmGeneration($files)
    {
        $this->stdout("Fixtures will be generated under the path: \n", Console::FG_YELLOW);
        $this->stdout("\t" . Aabc::getAlias($this->fixtureDataPath) . "\n\n", Console::FG_GREEN);
        $this->stdout("Templates will be taken from path: \n", Console::FG_YELLOW);
        $this->stdout("\t" . Aabc::getAlias($this->templatePath) . "\n\n", Console::FG_GREEN);

        foreach ($files as $fileName) {
            $this->stdout("\t* " . $fileName . "\n", Console::FG_GREEN);
        }

        return $this->confirm('Generate above fixtures?');
    }

}
