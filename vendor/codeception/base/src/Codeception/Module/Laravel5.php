<?php
namespace Codeception\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Connector\Laravel5 as LaravelConnector;
use Codeception\Lib\Framework;
use Codeception\Lib\Interfaces\ActiveRecord;
use Codeception\Lib\Interfaces\PartedModule;
use Codeception\Lib\ModuleContainer;
use Codeception\Subscriber\ErrorHandler;
use Codeception\Util\ReflectionHelper;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model as EloquentModel;


class Laravel5 extends Framework implements ActiveRecord, PartedModule
{

    
    public $app;

    
    public $config = [];

    
    public function __construct(ModuleContainer $container, $config = null)
    {
        $this->config = array_merge(
            [
                'cleanup' => true,
                'run_database_migrations' => false,
                'database_migrations_path' => '',
                'run_database_seeder' => false,
                'database_seeder_class' => '',
                'environment_file' => '.env',
                'bootstrap' => 'bootstrap' . DIRECTORY_SEPARATOR . 'app.php',
                'root' => '',
                'packages' => 'workbench',
                'vendor_dir' => 'vendor',
                'disable_exception_handling' => true,
                'disable_middleware' => false,
                'disable_events' => false,
                'disable_model_events' => false,
            ],
            (array)$config
        );

        $projectDir = explode($this->config['packages'], \Codeception\Configuration::projectDir())[0];
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
        $this->revertErrorHandler();
    }

    
    public function _before(\Codeception\TestInterface $test)
    {
        $this->client = new LaravelConnector($this);

        // Database migrations and seeder should run before database cleanup transaction starts
        if ($this->config['run_database_migrations']) {
            $this->callArtisan('migrate', ['--path' => $this->config['database_migrations_path']]);
        }

        if ($this->config['run_database_seeder']) {
            $this->callArtisan('db:seed', ['--class' => $this->config['database_seeder_class']]);
        }

        if (isset($this->app['db']) && $this->config['cleanup']) {
            $this->app['db']->beginTransaction();
        }
    }

    
    public function _after(\Codeception\TestInterface $test)
    {
        if (isset($this->app['db']) && $this->config['cleanup']) {
            $this->app['db']->rollback();
        }

        // disconnect from DB to prevent "Too many connections" issue
        if (isset($this->app['db'])) {
            $this->app['db']->disconnect();
        }
    }

    
    protected function checkBootstrapFileExists()
    {
        $bootstrapFile = $this->config['bootstrap_file'];

        if (!file_exists($bootstrapFile)) {
            throw new ModuleConfigException(
                $this,
                "Laravel bootstrap file not found in $bootstrapFile.\n"
                . "Please provide a valid path to it using 'bootstrap' config param. "
            );
        }
    }

    
    protected function registerAutoloaders()
    {
        require $this->config['project_dir'] . $this->config['vendor_dir'] . DIRECTORY_SEPARATOR . 'autoload.php';

        \Illuminate\Support\ClassLoader::register();
    }

    
    protected function revertErrorHandler()
    {
        $handler = new ErrorHandler();
        set_error_handler(array($handler, 'errorHandler'));
    }

    
    public function getApplication()
    {
        return $this->app;
    }

    
    public function setApplication($app)
    {
        $this->app = $app;
    }

    
    public function enableExceptionHandling()
    {
        $this->client->enableExceptionHandling();
    }

    
    public function disableExceptionHandling()
    {
        $this->client->disableExceptionHandling();
    }

    
    public function disableMiddleware()
    {
        $this->client->disableMiddleware();
    }

    
    public function disableEvents()
    {
        $this->client->disableEvents();
    }

    
    public function disableModelEvents()
    {
        $this->client->disableModelEvents();
    }

    
    public function seeEventTriggered($events)
    {
        $events = is_array($events) ? $events : func_get_args();

        foreach ($events as $event) {
            if (!$this->client->eventTriggered($event)) {
                if (is_object($event)) {
                    $event = get_class($event);
                }

                $this->fail("The '$event' event did not trigger");
            }
        }
    }

    
    public function dontSeeEventTriggered($events)
    {
        $events = is_array($events) ? $events : func_get_args();

        foreach ($events as $event) {
            if ($this->client->eventTriggered($event)) {
                if (is_object($event)) {
                    $event = get_class($event);
                }

                $this->fail("The '$event' event triggered");
            }
        }
    }

    
    public function callArtisan($command, $parameters = [])
    {
        $console = $this->app->make('Illuminate\Contracts\Console\Kernel');
        $console->call($command, $parameters);

        return trim($console->output());
    }

    
    public function amOnRoute($routeName, $params = [])
    {
        $route = $this->getRouteByName($routeName);

        $absolute = !is_null($route->domain());
        $url = $this->app['url']->route($routeName, $params, $absolute);
        $this->amOnPage($url);
    }

    
    public function seeCurrentRouteIs($routeName)
    {
        $this->getRouteByName($routeName); // Fails if route does not exists

        $currentRoute = $this->app->request->route();
        $currentRouteName = $currentRoute ? $currentRoute->getName() : '';

        if ($currentRouteName != $routeName) {
            $message = empty($currentRouteName)
                ? "Current route has no name"
                : "Current route is \"$currentRouteName\"";
            $this->fail($message);
        }
    }

    
    public function amOnAction($action, $params = [])
    {
        $route = $this->getRouteByAction($action);
        $absolute = !is_null($route->domain());
        $url = $this->app['url']->action($action, $params, $absolute);

        $this->amOnPage($url);
    }

    
    public function seeCurrentActionIs($action)
    {
        $this->getRouteByAction($action); // Fails if route does not exists
        $currentRoute = $this->app->request->route();
        $currentAction = $currentRoute ? $currentRoute->getActionName() : '';
        $currentAction = ltrim(str_replace($this->getRootControllerNamespace(), "", $currentAction), '\\');

        if ($currentAction != $action) {
            $this->fail("Current action is \"$currentAction\"");
        }
    }

    
    protected function getRouteByName($routeName)
    {
        if (!$route = $this->app['routes']->getByName($routeName)) {
            $this->fail("Route with name '$routeName' does not exist");
        }

        return $route;
    }

    
    protected function getRouteByAction($action)
    {
        $namespacedAction = $this->actionWithNamespace($action);

        if (!$route = $this->app['routes']->getByAction($namespacedAction)) {
            $this->fail("Action '$action' does not exist");
        }

        return $route;
    }

    
    protected function actionWithNamespace($action)
    {
        $rootNamespace = $this->getRootControllerNamespace();

        if ($rootNamespace && !(strpos($action, '\\') === 0)) {
            return $rootNamespace . '\\' . $action;
        } else {
            return trim($action, '\\');
        }
    }

    
    protected function getRootControllerNamespace()
    {
        $urlGenerator = $this->app['url'];
        $reflection = new \ReflectionClass($urlGenerator);

        $property = $reflection->getProperty('rootNamespace');
        $property->setAccessible(true);

        return $property->getValue($urlGenerator);
    }

    
    public function seeInSession($key, $value = null)
    {
        if (is_array($key)) {
            $this->seeSessionHasValues($key);
            return;
        }

        if (! $this->app['session']->has($key)) {
            $this->fail("No session variable with key '$key'");
        }

        if (! is_null($value)) {
            $this->assertEquals($value, $this->app['session']->get($key));
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

    
    public function seeFormHasErrors()
    {
        $viewErrorBag = $this->app->make('view')->shared('errors');
        if (count($viewErrorBag) == 0) {
            $this->fail("There are no form errors");
        }
    }

    
    public function dontSeeFormErrors()
    {
        $viewErrorBag = $this->app->make('view')->shared('errors');
        if (count($viewErrorBag) > 0) {
            $this->fail("Found the following form errors: \n\n" . $viewErrorBag->toJson(JSON_PRETTY_PRINT));
        }
    }

    
    public function seeFormErrorMessages(array $bindings)
    {
        foreach ($bindings as $key => $value) {
            $this->seeFormErrorMessage($key, $value);
        }
    }

    
    public function seeFormErrorMessage($key, $expectedErrorMessage = null)
    {
        $viewErrorBag = $this->app['view']->shared('errors');

        if (!($viewErrorBag->has($key))) {
            $this->fail("No form error message for key '$key'\n");
        }

        if (! is_null($expectedErrorMessage)) {
            $this->assertContains($expectedErrorMessage, $viewErrorBag->first($key));
        }
    }

    
    public function amLoggedAs($user, $driver = null)
    {
        $guard = $auth = $this->app['auth'];

        if (method_exists($auth, 'driver')) {
            $guard = $auth->driver($driver);
        }

        if (method_exists($auth, 'guard')) {
            $guard = $auth->guard($driver);
        }

        if ($user instanceof Authenticatable) {
            $guard->login($user);
            return;
        }

        if (! $guard->attempt($user)) {
            $this->fail("Failed to login with credentials " . json_encode($user));
        }
    }

    
    public function logout()
    {
        $this->app['auth']->logout();
    }

    
    public function seeAuthentication($guard = null)
    {
        $auth = $this->app['auth'];

        if (method_exists($auth, 'guard')) {
            $auth = $auth->guard($guard);
        }

        if (! $auth->check()) {
            $this->fail("There is no authenticated user");
        }
    }

    
    public function dontSeeAuthentication($guard = null)
    {
        $auth = $this->app['auth'];

        if (method_exists($auth, 'guard')) {
            $auth = $auth->guard($guard);
        }

        if ($auth->check()) {
            $this->fail("There is an authenticated user");
        }
    }

    
    public function grabService($class)
    {
        return $this->app[$class];
    }

    
    public function haveBinding($abstract, $concrete)
    {
        $this->client->haveBinding($abstract, $concrete);
    }

    
    public function haveSingleton($abstract, $concrete)
    {
        $this->client->haveBinding($abstract, $concrete, true);
    }

    
    public function haveContextualBinding($concrete, $abstract, $implementation)
    {
        $this->client->haveContextualBinding($concrete, $abstract, $implementation);
    }

    
    public function haveInstance($abstract, $instance)
    {
        $this->client->haveInstance($abstract, $instance);
    }

    
    public function haveRecord($table, $attributes = [])
    {
        if (class_exists($table)) {
            $model = new $table;

            if (! $model instanceof EloquentModel) {
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
            if (! $this->findModel($table, $attributes)) {
                $this->fail("Could not find $table with " . json_encode($attributes));
            }
        } elseif (! $this->findRecord($table, $attributes)) {
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
            if (! $model = $this->findModel($table, $attributes)) {
                $this->fail("Could not find $table with " . json_encode($attributes));
            }

            return $model;
        }

        if (! $record = $this->findRecord($table, $attributes)) {
            $this->fail("Could not find matching record in table '$table'");
        }

        return $record;
    }

    
    public function seeNumRecords($expectedNum, $table, $attributes = [])
    {
        if (class_exists($table)) {
            $currentNum = $this->countModels($table, $attributes);
            if ($currentNum != $expectedNum) {
                $this->fail("The number of found $table ($currentNum) does not match expected number $expectedNum with " . json_encode($attributes));
            }
        } else {
            $currentNum = $this->countRecords($table, $attributes);
            if ($currentNum != $expectedNum) {
                $this->fail("The number of found records ($currentNum) does not match expected number $expectedNum in table $table with " . json_encode($attributes));
            }
        }
    }

    
    public function grabNumRecords($table, $attributes = [])
    {
        return class_exists($table)? $this->countModels($table, $attributes) : $this->countRecords($table, $attributes);
    }

    
    protected function findModel($modelClass, $attributes = [])
    {
        $query = $this->getQueryBuilderFromModel($modelClass);
        foreach ($attributes as $key => $value) {
            $query->where($key, $value);
        }

        return $query->first();
    }

    
    protected function findRecord($table, $attributes = [])
    {
        $query = $this->getQueryBuilderFromTable($table);
        foreach ($attributes as $key => $value) {
            $query->where($key, $value);
        }

        return (array) $query->first();
    }

    
    protected function countModels($modelClass, $attributes = [])
    {
        $query = $this->getQueryBuilderFromModel($modelClass);
        foreach ($attributes as $key => $value) {
            $query->where($key, $value);
        }

        return $query->count();
    }

    
    protected function countRecords($table, $attributes = [])
    {
        $query = $this->getQueryBuilderFromTable($table);
        foreach ($attributes as $key => $value) {
            $query->where($key, $value);
        }

        return $query->count();
    }

    
    protected function getQueryBuilderFromModel($modelClass)
    {
        $model = new $modelClass;

        if (!$model instanceof EloquentModel) {
            throw new \RuntimeException("Class $modelClass is not an Eloquent model");
        }

        return $model->newQuery();
    }

    
    protected function getQueryBuilderFromTable($table)
    {
        return $this->app['db']->table($table);
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
        if (! function_exists('factory')) {
            throw new ModuleException($this, 'The factory() method does not exist. ' .
                'This functionality relies on Laravel model factories, which were introduced in Laravel 5.1.');
        }

        return factory($model, $name, $times);
    }

    
    protected function getInternalDomains()
    {
        $internalDomains = [$this->getApplicationDomainRegex()];

        foreach ($this->app['routes'] as $route) {
            if (!is_null($route->domain())) {
                $internalDomains[] = $this->getDomainRegex($route);
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

    
    private function getDomainRegex($route)
    {
        ReflectionHelper::invokePrivateMethod($route, 'compileRoute');
        $compiledRoute = ReflectionHelper::readPrivateProperty($route, 'compiled');

        return $compiledRoute->getHostRegex();
    }
}
