<?php
namespace Codeception\Module;

use Codeception\Configuration;
use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Connector\Aabc2 as Aabc2Connector;
use Codeception\Lib\Framework;
use Codeception\Lib\Interfaces\ActiveRecord;
use Codeception\Lib\Interfaces\PartedModule;
use Codeception\Lib\Notification;
use Codeception\TestInterface;
use Aabc;
use aabc\db\ActiveRecordInterface;


class Aabc2 extends Framework implements ActiveRecord, PartedModule
{
    const TEST_FIXTURES_METHOD = '_fixtures';

    
    protected $config = [
        'cleanup'     => true,
        'entryScript' => '',
        'entryUrl'    => 'http://localhost/index-test.php',
    ];

    protected $requiredFields = ['configFile'];
    protected $transaction;

    
    public $app;

    
    public $loadedFixtures = [];

    public function _initialize()
    {
        if (!is_file(codecept_root_dir() . $this->config['configFile'])) {
            throw new ModuleConfigException(
                __CLASS__,
                "The application config file does not exist: " . codecept_root_dir() . $this->config['configFile']
            );
        }
        $this->defineConstants();
    }

    public function _before(TestInterface $test)
    {
        $entryUrl = $this->config['entryUrl'];
        $entryFile = $this->config['entryScript'] ?: basename($entryUrl);
        $entryScript = $this->config['entryScript'] ?: parse_url($entryUrl, PHP_URL_PATH);

        $this->client = new Aabc2Connector();
        $this->client->defaultServerVars = [
            'SCRIPT_FILENAME' => $entryFile,
            'SCRIPT_NAME'     => $entryScript,
            'SERVER_NAME'     => parse_url($entryUrl, PHP_URL_HOST),
            'SERVER_PORT'     => parse_url($entryUrl, PHP_URL_PORT) ?: '80',
        ];
        $this->client->defaultServerVars['HTTPS'] = parse_url($entryUrl, PHP_URL_SCHEME) === 'https';
        $this->client->restoreServerVars();
        $this->client->configFile = Configuration::projectDir() . $this->config['configFile'];
        $this->app = $this->client->getApplication();

        // load fixtures before db transaction
        if ($test instanceof \Codeception\Test\Cest) {
            $this->loadFixtures($test->getTestClass());
        } else {
            $this->loadFixtures($test);
        }

        if ($this->config['cleanup'] && $this->app->has('db') && $this->app->db instanceof \aabc\db\Connection) {
            $this->transaction = $this->app->db->beginTransaction();
        }
    }

    
    private function loadFixtures($test)
    {
        if (method_exists($test, self::TEST_FIXTURES_METHOD)) {
            $this->haveFixtures(call_user_func([$test, self::TEST_FIXTURES_METHOD]));
        }
    }

    public function _after(\Codeception\TestInterface $test)
    {
        $_SESSION = [];
        $_FILES = [];
        $_GET = [];
        $_POST = [];
        $_COOKIE = [];
        $_REQUEST = [];

        foreach ($this->loadedFixtures as $fixture) {
            $fixture->unloadFixtures();
        }
        $this->loadedFixtures = [];

        if ($this->transaction && $this->config['cleanup']) {
            $this->transaction->rollback();
        }

        if ($this->client) {
            $this->client->resetPersistentVars();
        }

        if (isset(\Aabc::$app) && \Aabc::$app->has('session', true)) {
            \Aabc::$app->session->close();
        }

        // Close connections if exists
        if (isset(\Aabc::$app) && \Aabc::$app->has('db', true)) {
            \Aabc::$app->db->close();
        }

        parent::_after($test);
    }

    public function _parts()
    {
        return ['orm', 'init', 'fixtures', 'email'];
    }

    
    public function amLoggedInAs($user)
    {
        if (!Aabc::$app->has('user')) {
            throw new ModuleException($this, 'User component is not loaded');
        }
        if ($user instanceof \aabc\web\IdentityInterface) {
            $identity = $user;
        } else {
            // class name implementing IdentityInterface
            $identityClass = Aabc::$app->user->identityClass;
            $identity = call_user_func([$identityClass, 'findIdentity'], $user);
        }
        Aabc::$app->user->login($identity);
    }

    
    public function haveFixtures($fixtures)
    {
        if (empty($fixtures)) {
            return;
        }
        $fixturesStore = new Aabc2Connector\FixturesStore($fixtures);
        $fixturesStore->loadFixtures();
        $this->loadedFixtures[] = $fixturesStore;
    }

    
    public function grabFixtures()
    {
        return call_user_func_array(
            'array_merge',
            array_map( // merge all fixtures from all fixture stores
                function ($fixturesStore) {
                    return $fixturesStore->getFixtures();
                },
                $this->loadedFixtures
            )
        );
    }

    
    public function grabFixture($name, $index = null)
    {
        $fixtures = $this->grabFixtures();
        if (!isset($fixtures[$name])) {
            throw new ModuleException($this, "Fixture $name is not loaded");
        }
        $fixture = $fixtures[$name];
        if ($index === null) {
            return $fixture;
        }
        if ($fixture instanceof \aabc\test\BaseActiveFixture) {
            return $fixture->getModel($index);
        }
        throw new ModuleException($this, "Fixture $name is not an instance of ActiveFixture and can't be loaded with scond parameter");
    }

    
    public function haveRecord($model, $attributes = [])
    {
        
        $record = $this->getModelRecord($model);
        $record->setAttributes($attributes, false);
        $res = $record->save(false);
        if (!$res) {
            $this->fail("Record $model was not saved");
        }
        return $record->primaryKey;
    }

    
    public function seeRecord($model, $attributes = [])
    {
        $record = $this->findRecord($model, $attributes);
        if (!$record) {
            $this->fail("Couldn't find $model with " . json_encode($attributes));
        }
        $this->debugSection($model, json_encode($record));
    }

    
    public function dontSeeRecord($model, $attributes = [])
    {
        $record = $this->findRecord($model, $attributes);
        $this->debugSection($model, json_encode($record));
        if ($record) {
            $this->fail("Unexpectedly managed to find $model with " . json_encode($attributes));
        }
    }

    
    public function grabRecord($model, $attributes = [])
    {
        return $this->findRecord($model, $attributes);
    }

    protected function findRecord($model, $attributes = [])
    {
        $this->getModelRecord($model);
        return call_user_func([$model, 'find'])
            ->where($attributes)
            ->one();
    }

    protected function getModelRecord($model)
    {
        if (!class_exists($model)) {
            throw new \RuntimeException("Model $model does not exist");
        }
        $record = new $model;
        if (!$record instanceof ActiveRecordInterface) {
            throw new \RuntimeException("Model $model is not implement interface \\aabc\\db\\ActiveRecordInterface");
        }
        return $record;
    }

    
    public function amOnRoute($route, array $params = [])
    {
        array_unshift($params, $route);
        $this->amOnPage($params);
    }
    
    
    protected function clientRequest($method, $uri, array $parameters = [], array $files = [], array $server = [], $content = null, $changeHistory = true)
    {
        if (is_array($uri)) {
            $uri = Aabc::$app->getUrlManager()->createUrl($uri);
        }
        return parent::clientRequest($method, $uri, $parameters, $files, $server, $content, $changeHistory);
    }

    
    public function grabComponent($component)
    {
        if (!Aabc::$app->has($component)) {
            throw new ModuleException($this, "Component $component is not avilable in current application");
        }
        return Aabc::$app->get($component);
    }

    
    public function seeEmailIsSent($num = null)
    {
        if ($num === null) {
            $this->assertNotEmpty($this->grabSentEmails(), 'emails were sent');
            return;
        }
        $this->assertEquals($num, count($this->grabSentEmails()), 'number of sent emails is equal to ' . $num);
    }

    
    public function dontSeeEmailIsSent()
    {
        $this->seeEmailIsSent(0);
    }

    
    public function grabSentEmails()
    {
        $mailer = $this->grabComponent('mailer');
        if (!$mailer instanceof Aabc2Connector\TestMailer) {
            throw new ModuleException($this, "Mailer module is not mocked, can't test emails");
        }
        return $mailer->getSentMessages();
    }

    
    public function grabLastSentEmail()
    {
        $this->seeEmailIsSent();
        $messages = $this->grabSentEmails();
        return end($messages);
    }

    
    private function getDomainRegex($template)
    {
        if (preg_match('#https?://(.*)#', $template, $matches)) {
            $template = $matches[1];
        }
        $parameters = [];
        if (strpos($template, '<') !== false) {
            $template = preg_replace_callback(
                '/<(?:\w+):?([^>]+)?>/u',
                function ($matches) use (&$parameters) {
                    $key = '#' . count($parameters) . '#';
                    $parameters[$key] = isset($matches[1]) ? $matches[1] : '\w+';
                    return $key;
                },
                $template
            );
        }
        $template = preg_quote($template);
        $template = strtr($template, $parameters);
        return '/^' . $template . '$/u';
    }

    
    public function getInternalDomains()
    {
        $domains = [$this->getDomainRegex(Aabc::$app->urlManager->hostInfo)];

        if (Aabc::$app->urlManager->enablePrettyUrl) {
            foreach (Aabc::$app->urlManager->rules as $rule) {
                
                if (isset($rule->host)) {
                    $domains[] = $this->getDomainRegex($rule->host);
                }
            }
        }
        return array_unique($domains);
    }

    private function defineConstants()
    {
        defined('AABC_DEBUG') or define('AABC_DEBUG', true);
        defined('AABC_ENV') or define('AABC_ENV', 'test');
        defined('AABC_ENABLE_ERROR_HANDLER') or define('AABC_ENABLE_ERROR_HANDLER', false);

        if (AABC_ENV !== 'test') {
            Notification::warning("AABC_ENV is not set to `test`, please add \n\n`define(\'AABC_ENV\', \'test\');`\n\nto bootstrap file", 'Aabc Framework');
        }
    }
}
