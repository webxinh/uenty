<?php
namespace Codeception\Module;

use Phalcon\Di;
use PDOException;
use Phalcon\Mvc\Url;
use Phalcon\DiInterface;
use Phalcon\Di\Injectable;
use Codeception\TestInterface;
use Codeception\Configuration;
use Codeception\Lib\Framework;
use Phalcon\Mvc\RouterInterface;
use Phalcon\Mvc\Model as PhalconModel;
use Phalcon\Mvc\Router\RouteInterface;
use Codeception\Util\ReflectionHelper;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Interfaces\ActiveRecord;
use Codeception\Lib\Interfaces\PartedModule;
use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Connector\Phalcon as PhalconConnector;


class Phalcon extends Framework implements ActiveRecord, PartedModule
{
    protected $config = [
        'bootstrap'  => 'app/config/bootstrap.php',
        'cleanup'    => true,
        'savepoints' => true,
    ];

    
    protected $bootstrapFile = null;

    
    public $di = null;

    
    public $client;

    
    public function _initialize()
    {
        $this->bootstrapFile = Configuration::projectDir() . $this->config['bootstrap'];

        if (!file_exists($this->bootstrapFile)) {
            throw new ModuleConfigException(
                __CLASS__,
                "Bootstrap file does not exist in " . $this->config['bootstrap'] . "\n"
                . "Please create the bootstrap file that returns Application object\n"
                . "And specify path to it with 'bootstrap' config\n\n"
                . "Sample bootstrap: \n\n<?php\n"
                . '$config = include __DIR__ . "/config.php";' . "\n"
                . 'include __DIR__ . "/loader.php";' . "\n"
                . '$di = new \Phalcon\DI\FactoryDefault();' . "\n"
                . 'include __DIR__ . "/services.php";' . "\n"
                . 'return new \Phalcon\Mvc\Application($di);'
            );
        }

        $this->client = new PhalconConnector();
    }

    
    public function _before(TestInterface $test)
    {
        
        $application = require $this->bootstrapFile;
        if (!$application instanceof Injectable) {
            throw new ModuleException(__CLASS__, 'Bootstrap must return \Phalcon\Di\Injectable object');
        }

        $this->di = $application->getDI();

        Di::reset();
        Di::setDefault($this->di);

        if ($this->di->has('session')) {
            // Destroy existing sessions of previous tests
            $this->di['session'] = new PhalconConnector\MemorySession();
        }

        if ($this->di->has('cookies')) {
            $this->di['cookies']->useEncryption(false);
        }

        if ($this->config['cleanup'] && $this->di->has('db')) {
            if ($this->config['savepoints']) {
                $this->di['db']->setNestedTransactionsWithSavepoints(true);
            }
            $this->di['db']->begin();
        }

        // localize
        $bootstrap = $this->bootstrapFile;
        $this->client->setApplication(function () use ($bootstrap) {
            $currentDi = Di::getDefault();
            
            $application = require $bootstrap;
            $di = $application->getDI();
            if ($currentDi->has('db')) {
                $di['db'] = $currentDi['db'];
            }
            if ($currentDi->has('session')) {
                $di['session'] = $currentDi['session'];
            }
            if ($di->has('cookies')) {
                $di['cookies']->useEncryption(false);
            }
            return $application;
        });
    }

    
    public function _after(TestInterface $test)
    {
        if ($this->config['cleanup'] && isset($this->di['db'])) {
            while ($this->di['db']->isUnderTransaction()) {
                $level = $this->di['db']->getTransactionLevel();
                try {
                    $this->di['db']->rollback(true);
                } catch (PDOException $e) {
                }
                if ($level == $this->di['db']->getTransactionLevel()) {
                    break;
                }
            }
            $this->di['db']->close();
        }
        $this->di = null;
        Di::reset();

        $_SESSION = $_FILES = $_GET = $_POST = $_COOKIE = $_REQUEST = [];
    }

    public function _parts()
    {
        return ['orm', 'services'];
    }

    
    public function getApplication()
    {
        return $this->client->getApplication();
    }

    
    public function haveInSession($key, $val)
    {
        $this->di->get('session')->set($key, $val);
        $this->debugSection('Session', json_encode($this->di['session']->toArray()));
    }

    
    public function seeInSession($key, $value = null)
    {
        $this->debugSection('Session', json_encode($this->di['session']->toArray()));

        if (is_array($key)) {
            $this->seeSessionHasValues($key);
            return;
        }

        if (!$this->di['session']->has($key)) {
            $this->fail("No session variable with key '$key'");
        }

        if (is_null($value)) {
            $this->assertTrue($this->di['session']->has($key));
        } else {
            $this->assertEquals($value, $this->di['session']->get($key));
        }
    }

    
    public function seeSessionHasValues(array $bindings)
    {
        foreach ($bindings as $key => $value) {
            if (is_int($key)) {
                $this->seeInSession($value);
            } else {
                $this->seeInSession($key, $value);
            }
        }
    }

    
    public function haveRecord($model, $attributes = [])
    {
        $record = $this->getModelRecord($model);
        $res = $record->save($attributes);
        $field = function ($field) {
            if (is_array($field)) {
                return implode(', ', $field);
            }

            return $field;
        };

        if (!$res) {
            $messages = $record->getMessages();
            $errors = [];
            foreach ($messages as $message) {
                
                $errors[] = sprintf(
                    '[%s] %s: %s',
                    $message->getType(),
                    $field($message->getField()),
                    $message->getMessage()
                );
            }

            $this->fail(sprintf("Record %s was not saved. Messages: \n%s", $model, implode(PHP_EOL, $errors)));

            return null;
        }

        $this->debugSection($model, json_encode($record));

        return $this->getModelIdentity($record);
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

    
    public function grabServiceFromContainer($service, array $parameters = [])
    {
        if (!$this->di->has($service)) {
            $this->fail("Service $service is not available in container");
        }

        return $this->di->get($service, $parameters);
    }

    
    public function grabServiceFromDi($service, array $parameters = [])
    {
        return $this->grabServiceFromContainer($service, $parameters);
    }

    
    public function addServiceToContainer($name, $definition, $shared = false)
    {
        try {
            $service = $this->di->set($name, $definition, $shared);
            return $service->resolve();
        } catch (\Exception $e) {
            $this->fail($e->getMessage());

            return null;
        }
    }

    
    public function haveServiceInDi($name, $definition, $shared = false)
    {
        return $this->addServiceToContainer($name, $definition, $shared);
    }

    
    public function amOnRoute($routeName, array $params = [])
    {
        if (!$this->di->has('url')) {
            $this->fail('Unable to resolve "url" service.');
        }

        
        $url = $this->di->getShared('url');

        $urlParams = ['for' => $routeName];

        if ($params) {
            $urlParams += $params;
        }

        $this->amOnPage($url->get($urlParams, null, true));
    }

    
    public function seeCurrentRouteIs($routeName)
    {
        if (!$this->di->has('url')) {
            $this->fail('Unable to resolve "url" service.');
        }

        
        $url = $this->di->getShared('url');
        $this->seeCurrentUrlEquals($url->get(['for' => $routeName], null, true));
    }

    
    protected function findRecord($model, $attributes = [])
    {
        $this->getModelRecord($model);
        $query = [];
        foreach ($attributes as $key => $value) {
            $query[] = "$key = '$value'";
        }
        $squery = implode(' AND ', $query);
        $this->debugSection('Query', $squery);
        return call_user_func_array([$model, 'findFirst'], [$squery]);
    }

    
    protected function getModelRecord($model)
    {
        if (!class_exists($model)) {
            throw new ModuleException(__CLASS__, "Model $model does not exist");
        }

        $record = new $model;
        if (!$record instanceof PhalconModel) {
            throw new ModuleException(__CLASS__, "Model $model is not instance of \\Phalcon\\Mvc\\Model");
        }

        return $record;
    }

    
    protected function getModelIdentity(PhalconModel $model)
    {
        if (property_exists($model, 'id')) {
            return $model->id;
        }

        if (!$this->di->has('modelsMetadata')) {
            return null;
        }

        $primaryKeys = $this->di->get('modelsMetadata')->getPrimaryKeyAttributes($model);

        switch (count($primaryKeys)) {
            case 0:
                return null;
            case 1:
                return $model->{$primaryKeys[0]};
            default:
                return array_intersect_key(get_object_vars($model), array_flip($primaryKeys));
        }
    }

    
    protected function getInternalDomains()
    {
        $internalDomains = [$this->getApplicationDomainRegex()];

        
        $router = $this->di->get('router');

        if ($router instanceof RouterInterface) {
            
            $routes = $router->getRoutes();

            foreach ($routes as $route) {
                if ($route instanceof RouteInterface) {
                    $hostName = $route->getHostname();
                    if (!empty($hostName)) {
                        $internalDomains[] = '/^' . str_replace('.', '\.', $route->getHostname()) . '$/';
                    }
                }
            }
        }

        return array_unique($internalDomains);
    }

    
    private function getApplicationDomainRegex()
    {
        $server = ReflectionHelper::readPrivateProperty($this->client, 'server');
        $domain = $server['HTTP_HOST'];

        return '/^' . str_replace('.', '\.', $domain) . '$/';
    }
}
