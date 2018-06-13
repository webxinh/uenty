<?php
namespace Codeception\Lib;

use Codeception\Configuration;
use Codeception\Exception\ConfigurationException;
use Codeception\Exception\ModuleConflictException;
use Codeception\Exception\ModuleException;
use Codeception\Exception\ModuleRequireException;
use Codeception\Lib\Interfaces\ConflictsWithModule;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Lib\Interfaces\PartedModule;
use Codeception\Util\Annotation;


class ModuleContainer
{
    
    const MODULE_NAMESPACE = '\\Codeception\\Module\\';

    
    private $config;

    
    private $di;

    
    private $modules = [];

    
    private $active = [];

    
    private $actions = [];

    
    public function __construct(Di $di, $config)
    {
        $this->di = $di;
        $this->di->set($this);
        $this->config = $config;
    }

    
    public function create($moduleName, $active = true)
    {
        $this->active[$moduleName] = $active;

        $moduleClass = $this->getModuleClass($moduleName);
        if (!class_exists($moduleClass)) {
            throw new ConfigurationException("Module $moduleName could not be found and loaded");
        }

        $config = $this->getModuleConfig($moduleName);

        if (empty($config) && !$active) {
            // For modules that are a dependency of other modules we want to skip the validation of the config.
            // This config validation is performed in \Codeception\Module::__construct().
            // Explicitly setting $config to null skips this validation.
            $config = null;
        }

        $this->modules[$moduleName] = $module = $this->di->instantiate($moduleClass, [$this, $config], false);

        if ($this->moduleHasDependencies($module)) {
            $this->injectModuleDependencies($moduleName, $module);
        }

        // If module is not active its actions should not be included in the actor class
        $actions = $active ? $this->getActionsForModule($module, $config) : [];

        foreach ($actions as $action) {
            $this->actions[$action] = $moduleName;
        };

        return $module;
    }

    
    private function moduleHasDependencies($module)
    {
        if (!$module instanceof DependsOnModule) {
            return false;
        }

        return (bool) $module->_depends();
    }

    
    private function getActionsForModule($module, $config)
    {
        $reflectionClass = new \ReflectionClass($module);

        // Only public methods can be actions
        $methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);

        // Should this module be loaded partially?
        $configuredParts = null;
        if ($module instanceof PartedModule && isset($config['part'])) {
            $configuredParts = is_array($config['part']) ? $config['part'] : [$config['part']];
        }

        $actions = [];
        foreach ($methods as $method) {
            if ($this->includeMethodAsAction($module, $method, $configuredParts)) {
                $actions[] = $method->name;
            }
        }

        return $actions;
    }

    
    private function includeMethodAsAction($module, $method, $configuredParts = null)
    {
        // Filter out excluded actions
        if (in_array($method->name, $module::$excludeActions)) {
            return false;
        }

        // Keep only the $onlyActions if they are specified
        if ($module::$onlyActions && !in_array($method->name, $module::$onlyActions)) {
            return false;
        }

        // Do not include inherited actions if the static $includeInheritedActions property is set to false.
        // However, if an inherited action is also specified in the static $onlyActions property
        // it should be included as an action.
        if (!$module::$includeInheritedActions &&
            !in_array($method->name, $module::$onlyActions) &&
            $method->getDeclaringClass()->getName() != get_class($module)
        ) {
            return false;
        }

        // Do not include hidden methods, methods with a name starting with an underscore
        if (strpos($method->name, '_') === 0) {
            return false;
        };

        // If a part is configured for the module, only include actions from that part
        if ($configuredParts) {
            $moduleParts = Annotation::forMethod($module, $method->name)->fetchAll('part');
            if (!array_uintersect($moduleParts, $configuredParts, 'strcasecmp')) {
                return false;
            }
        }

        return true;
    }

    
    private function isHelper($moduleName)
    {
        return strpos($moduleName, '\\') !== false;
    }

    
    private function getModuleClass($moduleName)
    {
        if ($this->isHelper($moduleName)) {
            return $moduleName;
        }

        return self::MODULE_NAMESPACE . $moduleName;
    }

    
    public function hasModule($moduleName)
    {
        return isset($this->modules[$moduleName]);
    }

    
    public function getModule($moduleName)
    {
        if (!$this->hasModule($moduleName)) {
            throw new ModuleException(__CLASS__, "Module $moduleName couldn't be connected");
        }

        return $this->modules[$moduleName];
    }

    
    public function moduleForAction($action)
    {
        if (!isset($this->actions[$action])) {
            return null;
        }

        return $this->modules[$this->actions[$action]];
    }

    
    public function getActions()
    {
        return $this->actions;
    }

    
    public function all()
    {
        return $this->modules;
    }

    
    public function mock($moduleName, $mock)
    {
        $this->modules[$moduleName] = $mock;
    }

    
    private function injectModuleDependencies($moduleName, DependsOnModule $module)
    {
        $this->checkForMissingDependencies($moduleName, $module);

        if (!method_exists($module, '_inject')) {
            throw new ModuleException($module, 'Module requires method _inject to be defined to accept dependencies');
        }

        $dependencies = array_map(function ($dependency) {
            return $this->create($dependency, false);
        }, $this->getConfiguredDependencies($moduleName));

        call_user_func_array([$module, '_inject'], $dependencies);
    }

    
    private function checkForMissingDependencies($moduleName, DependsOnModule $module)
    {
        $dependencies = $this->getModuleDependencies($module);
        $configuredDependenciesCount = count($this->getConfiguredDependencies($moduleName));

        if ($configuredDependenciesCount < count($dependencies)) {
            $missingDependency = array_keys($dependencies)[$configuredDependenciesCount];

            $message = sprintf(
                "\nThis module depends on %s\n\n\n%s",
                $missingDependency,
                $this->getErrorMessageForDependency($module, $missingDependency)
            );

            throw new ModuleRequireException($moduleName, $message);
        }
    }

    
    private function getModuleDependencies(DependsOnModule $module)
    {
        $depends = $module->_depends();

        if (!$depends) {
            return [];
        }

        if (!is_array($depends)) {
            $message = sprintf("Method _depends of module '%s' must return an array", get_class($module));
            throw new ModuleException($module, $message);
        }

        return $depends;
    }

    
    private function getConfiguredDependencies($moduleName)
    {
        $config = $this->getModuleConfig($moduleName);

        if (!isset($config['depends'])) {
            return [];
        }

        return is_array($config['depends']) ? $config['depends'] : [$config['depends']];
    }

    
    private function getErrorMessageForDependency($module, $missingDependency)
    {
        $depends = $module->_depends();

        return $depends[$missingDependency];
    }

    
    private function getModuleConfig($moduleName)
    {
        $config = isset($this->config['modules']['config'][$moduleName])
            ? $this->config['modules']['config'][$moduleName]
            : [];

        if (!isset($this->config['modules']['enabled'])) {
            return $config;
        }

        if (!is_array($this->config['modules']['enabled'])) {
            return $config;
        }

        foreach ($this->config['modules']['enabled'] as $enabledModuleConfig) {
            if (!is_array($enabledModuleConfig)) {
                continue;
            }

            $enabledModuleName = key($enabledModuleConfig);
            if ($enabledModuleName === $moduleName) {
                return Configuration::mergeConfigs(reset($enabledModuleConfig), $config);
            }
        }

        return $config;
    }

    
    public function validateConflicts()
    {
        $canConflict = [];
        foreach ($this->modules as $moduleName => $module) {
            $parted = $module instanceof PartedModule && $module->_getConfig('part');

            if ($this->active[$moduleName] && !$parted) {
                $canConflict[] = $module;
            }
        }

        foreach ($canConflict as $module) {
            foreach ($canConflict as $otherModule) {
                $this->validateConflict($module, $otherModule);
            }
        }
    }

    
    private function validateConflict($module, $otherModule)
    {
        if ($module === $otherModule || !$module instanceof ConflictsWithModule) {
            return;
        }

        $conflicts = $this->normalizeConflictSpecification($module->_conflicts());
        if ($otherModule instanceof $conflicts) {
            throw new ModuleConflictException($module, $otherModule);
        }
    }

    
    private function normalizeConflictSpecification($conflicts)
    {
        if (interface_exists($conflicts) || class_exists($conflicts)) {
            return $conflicts;
        }

        if ($this->hasModule($conflicts)) {
            return $this->getModule($conflicts);
        }

        return $conflicts;
    }
}
