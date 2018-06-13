<?php
namespace Codeception\Lib\Connector;

use Codeception\Lib\Connector\Laravel5\ExceptionHandlerDecorator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;

class Laravel5 extends Client
{
    
    private $app;

    
    private $module;

    
    private $firstRequest = true;

    
    private $triggeredEvents = [];

    
    private $exceptionHandlingDisabled;

    
    private $middlewareDisabled;

    
    private $eventsDisabled;

    
    private $modelEventsDisabled;

    
    private $bindings = [];

    
    private $contextualBindings = [];

    
    private $instances = [];

    
    private $oldDb;

    
    public function __construct($module)
    {
        $this->module = $module;

        $this->exceptionHandlingDisabled = $this->module->config['disable_exception_handling'];
        $this->middlewareDisabled = $this->module->config['disable_middleware'];
        $this->eventsDisabled = $this->module->config['disable_events'];
        $this->modelEventsDisabled = $this->module->config['disable_model_events'];

        $this->initialize();

        $components = parse_url($this->app['config']->get('app.url', 'http://localhost'));
        if (array_key_exists('url', $this->module->config)) {
            $components = parse_url($this->module->config['url']);
        }
        $host = isset($components['host']) ? $components['host'] : 'localhost';

        parent::__construct($this->app, ['HTTP_HOST' => $host]);

        // Parent constructor defaults to not following redirects
        $this->followRedirects(true);
    }

    
    protected function doRequest($request)
    {
        if (!$this->firstRequest) {
            $this->initialize($request);
        }
        $this->firstRequest = false;

        $this->applyBindings();
        $this->applyContextualBindings();
        $this->applyInstances();

        $request = Request::createFromBase($request);
        $response = $this->kernel->handle($request);
        $this->app->make('Illuminate\Contracts\Http\Kernel')->terminate($request, $response);

        return $response;
    }

    
    protected function filterFiles(array $files)
    {
        $files = parent::filterFiles($files);

        if (! class_exists('Illuminate\Http\UploadedFile')) {
            // The \Illuminate\Http\UploadedFile class was introduced in Laravel 5.2.15,
            // so don't change the $files array if it does not exist.
            return $files;
        }

        return $this->convertToTestFiles($files);
    }

    
    private function convertToTestFiles(array $files)
    {
        $filtered = [];

        foreach ($files as $key => $value) {
            if (is_array($value)) {
                $filtered[$key] = $this->convertToTestFiles($value);
            } else {
                $filtered[$key] = UploadedFile::createFromBase($value, true);
            }
        }

        return $filtered;
    }

    
    private function initialize($request = null)
    {
        // Store a reference to the database object
        // so the database connection can be reused during tests
        $this->oldDb = null;
        if (isset($this->app['db']) && $this->app['db']->connection()) {
            $this->oldDb = $this->app['db'];
        }

        $this->app = $this->kernel = $this->loadApplication();

        // Set the request instance for the application,
        if (is_null($request)) {
            $appConfig = require $this->module->config['project_dir'] . 'config/app.php';
            $request = SymfonyRequest::create($appConfig['url']);
        }
        $this->app->instance('request', Request::createFromBase($request));

        // Reset the old database after the DatabaseServiceProvider ran.
        // This way other service providers that rely on the $app['db'] entry
        // have the correct instance available.
        if ($this->oldDb) {
            $this->app['events']->listen('Illuminate\Database\DatabaseServiceProvider', function () {
                $this->app->singleton('db', function () {
                    return $this->oldDb;
                });
            });
        }

        $this->app->make('Illuminate\Contracts\Http\Kernel')->bootstrap();

        // Record all triggered events by adding a wildcard event listener
        $this->app['events']->listen('*', function () {
            $this->triggeredEvents[] = $this->normalizeEvent($this->app['events']->firing());
        });

        // Replace the Laravel exception handler with our decorated exception handler,
        // so exceptions can be intercepted for the disable_exception_handling functionality.
        $decorator = new ExceptionHandlerDecorator($this->app['Illuminate\Contracts\Debug\ExceptionHandler']);
        $decorator->exceptionHandlingDisabled($this->exceptionHandlingDisabled);
        $this->app->instance('Illuminate\Contracts\Debug\ExceptionHandler', $decorator);

        if ($this->module->config['disable_middleware'] || $this->middlewareDisabled) {
            $this->app->instance('middleware.disable', true);
        }

        if ($this->module->config['disable_events'] || $this->eventsDisabled) {
            $this->mockEventDispatcher();
        }

        if ($this->module->config['disable_model_events'] || $this->modelEventsDisabled) {
            Model::unsetEventDispatcher();
        }

        $this->module->setApplication($this->app);
    }

    
    private function loadApplication()
    {
        $app = require $this->module->config['bootstrap_file'];
        $app->loadEnvironmentFrom($this->module->config['environment_file']);
        $app->instance('request', new Request());

        return $app;
    }

    
    private function mockEventDispatcher()
    {
        $mockGenerator = new \PHPUnit_Framework_MockObject_Generator;
        $mock = $mockGenerator->getMock('Illuminate\Contracts\Events\Dispatcher');

        // Even if events are disabled we still want to record the triggered events.
        // But by mocking the event dispatcher the wildcard listener registered in the initialize method is removed.
        // So to record the triggered events we have to catch the calls to the fire method of the event dispatcher mock.
        $callback = function ($event) {
            $this->triggeredEvents[] = $this->normalizeEvent($event);

            return [];
        };
        $mock->expects(new \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount)
            ->method('fire')
            ->will(new \PHPUnit_Framework_MockObject_Stub_ReturnCallback($callback));

        $this->app->instance('events', $mock);
    }

    
    private function normalizeEvent($event)
    {
        if (is_object($event)) {
            $event = get_class($event);
        }

        if (preg_match('/^bootstrapp(ing|ed): /', $event)) {
            return $event;
        }

        // Events can be formatted as 'event.name: parameters'
        $segments = explode(':', $event);

        return $segments[0];
    }

    
    private function applyBindings()
    {
        foreach ($this->bindings as $abstract => $binding) {
            list($concrete, $shared) = $binding;

            $this->app->bind($abstract, $concrete, $shared);
        }
    }

    
    private function applyContextualBindings()
    {
        foreach ($this->contextualBindings as $concrete => $bindings) {
            foreach ($bindings as $abstract => $implementation) {
                $this->app->addContextualBinding($concrete, $abstract, $implementation);
            }
        }
    }

    
    private function applyInstances()
    {
        foreach ($this->instances as $abstract => $instance) {
            $this->app->instance($abstract, $instance);
        }
    }

    //======================================================================
    // Public methods called by module
    //======================================================================

    
    public function eventTriggered($event)
    {
        $event = $this->normalizeEvent($event);

        foreach ($this->triggeredEvents as $triggeredEvent) {
            if ($event == $triggeredEvent || is_subclass_of($event, $triggeredEvent)) {
                return true;
            }
        }

        return false;
    }

    
    public function disableExceptionHandling()
    {
        $this->exceptionHandlingDisabled = true;
        $this->app['Illuminate\Contracts\Debug\ExceptionHandler']->exceptionHandlingDisabled(true);
    }

    
    public function enableExceptionHandling()
    {
        $this->exceptionHandlingDisabled = false;
        $this->app['Illuminate\Contracts\Debug\ExceptionHandler']->exceptionHandlingDisabled(false);
    }

    
    public function disableEvents()
    {
        $this->eventsDisabled = true;
        $this->mockEventDispatcher();
    }

    
    public function disableModelEvents()
    {
        $this->modelEventsDisabled = true;
        Model::unsetEventDispatcher();
    }

    /*
     * Disable middleware.
     */
    public function disableMiddleware()
    {
        $this->middlewareDisabled = true;
        $this->app->instance('middleware.disable', true);
    }

    
    public function haveBinding($abstract, $concrete, $shared = false)
    {
        $this->bindings[$abstract] = [$concrete, $shared];
    }

    
    public function haveContextualBinding($concrete, $abstract, $implementation)
    {
        if (! isset($this->contextualBindings[$concrete])) {
            $this->contextualBindings[$concrete] = [];
        }

        $this->contextualBindings[$concrete][$abstract] = $implementation;
    }

    
    public function haveInstance($abstract, $instance)
    {
        $this->instances[$abstract] = $instance;
    }
}
