<?php
namespace Codeception\Module;

use Codeception\Lib\Framework;
use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Interfaces\PartedModule;
use Codeception\TestInterface;
use Codeception\Lib\Connector\Aabc1 as Aabc1Connector;
use Codeception\Util\ReflectionHelper;
use Aabc;


class Aabc1 extends Framework implements PartedModule
{

    
    protected $requiredFields = ['appPath', 'url'];

    
    private $appSettings;

    private $_appConfig;

    public function _initialize()
    {
        if (!file_exists($this->config['appPath'])) {
            throw new ModuleConfigException(
                __CLASS__,
                "Couldn't load application config file {$this->config['appPath']}\n" .
                "Please provide application bootstrap file configured for testing"
            );
        }

        $this->appSettings = include($this->config['appPath']); //get application settings in the entry script

        // get configuration from array or file
        if (is_array($this->appSettings['config'])) {
            $this->_appConfig = $this->appSettings['config'];
        } else {
            if (!file_exists($this->appSettings['config'])) {
                throw new ModuleConfigException(
                    __CLASS__,
                    "Couldn't load configuration file from Aabc app file: {$this->appSettings['config']}\n" .
                    "Please provide valid 'config' parameter"
                );
            }
            $this->_appConfig = include($this->appSettings['config']);
        }

        if (!defined('AABC_ENABLE_EXCEPTION_HANDLER')) {
            define('AABC_ENABLE_EXCEPTION_HANDLER', false);
        }
        if (!defined('AABC_ENABLE_ERROR_HANDLER')) {
            define('AABC_ENABLE_ERROR_HANDLER', false);
        }

        $_SERVER['SCRIPT_NAME'] = parse_url($this->config['url'], PHP_URL_PATH);
        $_SERVER['SCRIPT_FILENAME'] = $this->config['appPath'];

        if (!function_exists('launch_codeception_aabc_bridge')) {
            throw new ModuleConfigException(
                __CLASS__,
                "Codeception-Aabc Bridge is not launched. In order to run tests you need to install "
                . "https://github.com/Codeception/AabcBridge Implement function 'launch_codeception_aabc_bridge' to "
                . "load all Codeception overrides"
            );
        }
        launch_codeception_aabc_bridge();

        Aabc::$enableIncludePath = false;
        Aabc::setApplication(null);
        Aabc::createApplication($this->appSettings['class'], $this->_appConfig);
    }

    /*
     * Create the client connector. Called before each test
     */
    public function _createClient()
    {
        $this->client = new Aabc1Connector();
        $this->client->setServerParameter("HTTP_HOST", parse_url($this->config['url'], PHP_URL_HOST));
        $this->client->appPath = $this->config['appPath'];
        $this->client->url = $this->config['url'];
        $this->client->appSettings = [
            'class'  => $this->appSettings['class'],
            'config' => $this->_appConfig,
        ];
    }

    public function _before(TestInterface $test)
    {
        $this->_createClient();
    }

    public function _after(TestInterface $test)
    {
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        $_COOKIE = [];
        $_REQUEST = [];
        Aabc::app()->session->close();
        parent::_after($test);
    }

    
    private function getDomainRegex($template, $parameters = [])
    {
        if ($host = parse_url($template, PHP_URL_HOST)) {
            $template = $host;
        }
        if (strpos($template, '<') !== false) {
            $template = str_replace(['<', '>'], '#', $template);
        }
        $template = preg_quote($template);
        foreach ($parameters as $name => $value) {
            $template = str_replace("#$name#", $value, $template);
        }
        return '/^' . $template . '$/u';
    }


    
    public function getInternalDomains()
    {
        $domains = [$this->getDomainRegex(Aabc::app()->request->getHostInfo())];
        if (Aabc::app()->urlManager->urlFormat === 'path') {
            $parent = Aabc::app()->urlManager instanceof \CUrlManager ? '\CUrlManager' : null;
            $rules = ReflectionHelper::readPrivateProperty(Aabc::app()->urlManager, '_rules', $parent);
            foreach ($rules as $rule) {
                if ($rule->hasHostInfo === true) {
                    $domains[] = $this->getDomainRegex($rule->template, $rule->params);
                }
            }
        }
        return array_unique($domains);
    }

    public function _parts()
    {
        return ['init', 'initialize'];
    }
}
