<?php


namespace aabc\log;

use Aabc;
use aabc\base\Component;
use aabc\base\ErrorHandler;


class Dispatcher extends Component
{
    
    public $targets = [];

    
    private $_logger;


    
    public function __construct($config = [])
    {
        // ensure logger gets set before any other config option
        if (isset($config['logger'])) {
            $this->setLogger($config['logger']);
            unset($config['logger']);
        }
        // connect logger and dispatcher
        $this->getLogger();

        parent::__construct($config);
    }

    
    public function init()
    {
        parent::init();

        foreach ($this->targets as $name => $target) {
            if (!$target instanceof Target) {
                $this->targets[$name] = Aabc::createObject($target);
            }
        }
    }

    
    public function getLogger()
    {
        if ($this->_logger === null) {
            $this->setLogger(Aabc::getLogger());
        }
        return $this->_logger;
    }

    
    public function setLogger($value)
    {
        if (is_string($value) || is_array($value)) {
            $value = Aabc::createObject($value);
        }
        $this->_logger = $value;
        $this->_logger->dispatcher = $this;
    }

    
    public function getTraceLevel()
    {
        return $this->getLogger()->traceLevel;
    }

    
    public function setTraceLevel($value)
    {
        $this->getLogger()->traceLevel = $value;
    }

    
    public function getFlushInterval()
    {
        return $this->getLogger()->flushInterval;
    }

    
    public function setFlushInterval($value)
    {
        $this->getLogger()->flushInterval = $value;
    }

    
    public function dispatch($messages, $final)
    {
        $targetErrors = [];
        foreach ($this->targets as $target) {
            if ($target->enabled) {
                try {
                    $target->collect($messages, $final);
                } catch (\Exception $e) {
                    $target->enabled = false;
                    $targetErrors[] = [
                        'Unable to send log via ' . get_class($target) . ': ' . ErrorHandler::convertExceptionToString($e),
                        Logger::LEVEL_WARNING,
                        __METHOD__,
                        microtime(true),
                        [],
                    ];
                }
            }
        }

        if (!empty($targetErrors)) {
            $this->dispatch($targetErrors, true);
        }
    }
}
