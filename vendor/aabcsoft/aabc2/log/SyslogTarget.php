<?php


namespace aabc\log;

use Aabc;
use aabc\helpers\VarDumper;


class SyslogTarget extends Target
{
    
    public $identity;
    
    public $facility = LOG_USER;
    
    public $options;

    
    private $_syslogLevels = [
        Logger::LEVEL_TRACE => LOG_DEBUG,
        Logger::LEVEL_PROFILE_BEGIN => LOG_DEBUG,
        Logger::LEVEL_PROFILE_END => LOG_DEBUG,
        Logger::LEVEL_PROFILE => LOG_DEBUG,
        Logger::LEVEL_INFO => LOG_INFO,
        Logger::LEVEL_WARNING => LOG_WARNING,
        Logger::LEVEL_ERROR => LOG_ERR,
    ];


    
    public function init()
    {
        parent::init();
        if ($this->options === null) {
            $this->options = LOG_ODELAY | LOG_PID;
        }
    }

    
    public function export()
    {
        openlog($this->identity, $this->options, $this->facility);
        foreach ($this->messages as $message) {
            syslog($this->_syslogLevels[$message[1]], $this->formatMessage($message));
        }
        closelog();
    }

    
    public function formatMessage($message)
    {
        list($text, $level, $category, $timestamp) = $message;
        $level = Logger::getLevelName($level);
        if (!is_string($text)) {
            // exceptions may not be serializable if in the call stack somewhere is a Closure
            if ($text instanceof \Throwable || $text instanceof \Exception) {
                $text = (string) $text;
            } else {
                $text = VarDumper::export($text);
            }
        }

        $prefix = $this->getMessagePrefix($message);
        return "{$prefix}[$level][$category] $text";
    }
}
