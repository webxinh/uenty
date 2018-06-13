<?php
namespace Codeception\Module;

use Codeception\Configuration;
use Codeception\Exception\ModuleException;
use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Connector\Lumen as LumenConnector;
use Codeception\Lib\Framework;
use Codeception\Lib\Interfaces\ActiveRecord;
use Codeception\Lib\Interfaces\PartedModule;
use Codeception\Lib\ModuleContainer;
use Codeception\Step;
use Codeception\TestInterface;
use Codeception\Util\ReflectionHelper;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model as EloquentModel;


class Lumen extends Framework implements ActiveRecord, PartedModule
{
    
    public $app;

    
    public $config = [];

    
    public function __construct(ModuleContainer $container, $config = null)
    {
        $this->config = array_merge(
            array(
                'cleanup' => true,
                'bootstrap' => 'bootstrap' . DIRECTORY_SEPARATOR . 'app.php',
                'root' => '',
                'packages' => 'workbench',
                'url' => 'http://localhost',
            ),
            (array)$config
        );

        $projectDir = explode($this->config['packages'], Configuration::projectDir())[0];
        $projectDir .= $this->config['root'];

        $this->config['project_dir'] = $projectDir;
        $this->config['bootstrap_file'] = $projectDir . $this->config['bootstrap'];

        parent::__construct($container);
    }

    
    public function _parts()
    {
        return ['orm'];
    }

    
    public function _initialize()
    {
        $this->checkBootstrapFileExists();
        $this->registerAutoloaders();
    }

    
    public function _before(TestInterface $test)
    {
        $this->client = new LumenConnector($this);

        if ($this->app['db'] && $this->config['cleanup']) {
            $this->app['db']->beginTransaction();
        }
    }

    
    public function _after(TestInterface $test)
    {
        if ($this->app['db'] && $this->config['cleanup']) {
            $this->app['db']->rollback();
        }

        // disconnect from DB to prevent "Too many connections" issue
        if ($this->app['db']) {
            $this->app['db']->disconnect();
        }
    }

    
    protected function checkBootstrapFileExists()
    {
        $bootstrapFile = $this->config['bootstrap_file'];

        if (!file_exists($bootstrapFile)) {
            throw new ModuleConfigException(
                $this->module,
                "Lumen bootstrap file not found in $bootstrapFile.\n"
                . "Please provide a valid path using the 'bootstrap' config param. "
            );
        }
    }

    
    protected function registerAutoloaders()
    {
        require $this->config['project_dir'] . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
    }

    
    public function getApplication()
    {
        return $this->app;
    }

    
    public function setApplication($app)
    {
        $this->app = $app;
    }

    
    public function amOnRoute($routeName, $params = [])
    {
        $route = $this->getRouteByName($routeName);

        if (!$route) {
            $this->fail("Could not find route with name '$routeName'");
        }

        $url = $this->generateUrlForRoute($route, $params);
        $this->amOnPage($url);
    }

    
    private function getRouteByName($routeName)
    {
        foreach ($this->app->getRoutes() as $route) {
            if ($route['method'] != 'GET') {
                return;
            }

            if (isset($route['action']['as']) && $route['action']['as'] == $routeName) {
                return $route;
            }
        }

        return null;
    }

    
    private function generateUrlForRoute($route, $params)
    {
        $url = $route['uri'];

        while (count($params) > 0) {
            $param = array_shift($params);
            $url = preg_replace('/{.+?}/', $param, $url, 1);
        }

        return $url;
    }

    
    public function amLoggedAs($user, $driver = null)
    {
        if (!$user instanceof Authenticatable) {
            $this->fail(
                'The user passed to amLoggedAs() should be an instance of \\Illuminate\\Contracts\\Auth\\Authenticable'
            );
        }

        $guard = $auth = $this->app['auth'];

        if (method_exists($auth, 'driver')) {
            $guard = $auth->driver($driver);
        }

        if (method_exists($auth, 'guard')) {
            $guard = $auth->guard($driver);
        }

        $guard->setUser($user);
    }

    
    public function seeAuthentication()
    {
        $this->assertTrue($this->app['auth']->check(), 'User is not logged in');
    }
    
    public function dontSeeAuthentication()
    {
        $this->assertFalse($this->app['auth']->check(), 'User is logged in');
    }

    
    public function grabService($class)
    {
        return $this->app[$class];
    }

    
    public function haveRecord($table, $attributes = [])
    {
        if (class_exists($table)) {
            $model = new $table;

            if (!$model instanceof EloquentModel) {
                throw new \RuntimeException("Class $table is not an Eloquent model");
            }

            $model->fill($attributes)->save();

            return $model;
        }

        try {
            return $this->app['db']->table($table)->insertGetId($attributes);
        } catch (\Exception $e) {
            $this->fail("Could not insert record into table '$table':\n\n" . $e->getMessage());
        }
    }

    
    public function seeRecord($table, $attributes = [])
    {
        if (class_exists($table)) {
            if (!$this->findModel($table, $attributes)) {
                $this->fail("Could not find $table with " . json_encode($attributes));
            }
        } elseif (!$this->findRecord($table, $attributes)) {
            $this->fail("Could not find matching record in table '$table'");
        }
    }

    
    public function dontSeeRecord($table, $attributes = [])
    {
        if (class_exists($table)) {
            if ($this->findModel($table, $attributes)) {
                $this->fail("Unexpectedly found matching $table with " . json_encode($attributes));
            }
        } elseif ($this->findRecord($table, $attributes)) {
            $this->fail("Unexpectedly found matching record in table '$table'");
        }
    }

    
    public function grabRecord($table, $attributes = [])
    {
        if (class_exists($table)) {
            if (!$model = $this->findModel($table, $attributes)) {
                $this->fail("Could not find $table with " . json_encode($attributes));
            }

            return $model;
        }

        if (!$record = $this->findRecord($table, $attributes)) {
            $this->fail("Could not find matching record in table '$table'");
        }

        return $record;
    }

    
    protected function findModel($modelClass, $attributes = [])
    {
        $model = new $modelClass;

        if (!$model instanceof EloquentModel) {
            throw new \RuntimeException("Class $modelClass is not an Eloquent model");
        }

        $query = $model->newQuery();
        foreach ($attributes as $key => $value) {
            $query->where($key, $value);
        }

        return $query->first();
    }

    
    protected function findRecord($table, $attributes = [])
    {
        $query = $this->app['db']->table($table);
        foreach ($attributes as $key => $value) {
            $query->where($key, $value);
        }

        return (array)$query->first();
    }

    
    public function have($model, $attributes = [], $name = 'default')
    {
        try {
            return $this->modelFactory($model, $name)->create($attributes);
        } catch (\Exception $e) {
            $this->fail("Could not create model: \n\n" . get_class($e) . "\n\n" . $e->getMessage());
        }
    }

    
    public function haveMultiple($model, $times, $attributes = [], $name = 'default')
    {
        try {
            return $this->modelFactory($model, $name, $times)->create($attributes);
        } catch (\Exception $e) {
            $this->fail("Could not create model: \n\n" . get_class($e) . "\n\n" . $e->getMessage());
        }
    }

    
    protected function modelFactory($model, $name, $times = 1)
    {
        if (!function_exists('factory')) {
            throw new ModuleException($this, 'The factory() method does not exist. ' .
                'This functionality relies on Lumen model factories, which were introduced in Lumen 5.1.');
        }

        return factory($model, $name, $times);
    }

    
    protected function getInternalDomains()
    {
        $server = ReflectionHelper::readPrivateProperty($this->client, 'server');

        return ['/^' . str_replace('.', '\.', $server['HTTP_HOST']) . '$/'];
    }
}
