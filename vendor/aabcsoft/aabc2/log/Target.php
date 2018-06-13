<?php


namespace aabc\log;

use Aabc;
use aabc\base\Component;
use aabc\base\InvalidConfigException;
use aabc\helpers\ArrayHelper;
use aabc\helpers\VarDumper;
use aabc\web\Request;


abstract class Target extends Component
{
    
    public $enabled = true;
    
    public $categories = [];
    
    public $except = [];
    
    public $logVars = ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_SERVER'];
    
    public $prefix;
    
    public $exportInterval = 1000;
    
    public $messages = [];

    private $_levels = 0;


    
    abstract public function export();

    
    public function collect($messages, $final)
    {
        $this->messages = array_merge($this->messages, static::filterMessages($messages, $this->getLevels(), $this->categories, $this->except));
        $count = count($this->messages);
        if ($count > 0 && ($final || $this->exportInterval > 0 && $count >= $this->exportInterval)) {
            if (($context = $this->getContextMessage()) !== '') {
                $this->messages[] = [$context, Logger::LEVEL_INFO, 'application', AABC_BEGIN_TIME];
            }
            // set exportInterval to 0 to avoid triggering export again while exporting
            $oldExportInterval = $this->exportInterval;
            $this->exportInterval = 0;
            $this->export();
            $this->exportInterval = $oldExportInterval;

            $this->messages = [];
        }
    }

    
    protected function getContextMessage()
    {
        $context = ArrayHelper::filter($GLOBALS, $this->logVars);
        $result = [];
        foreach ($context as $key => $value) {
            $result[] = "\${$key} = " . VarDumper::dumpAsString($value);
        }
        return implode("\n\n", $result);
    }

    
    public function getLevels()
    {
        return $this->_levels;
    }

    
    public function setLevels($levels)
    {
        static $levelMap = [
            'error' => Logger::LEVEL_ERROR,
            'warning' => Logger::LEVEL_WARNING,
            'info' => Logger::LEVEL_INFO,
            'trace' => Logger::LEVEL_TRACE,
            'profile' => Logger::LEVEL_PROFILE,
        ];
        if (is_array($levels)) {
            $this->_levels = 0;
            foreach ($levels as $level) {
                if (isset($levelMap[$level])) {
                    $this->_levels |= $levelMap[$level];
                } else {
                    throw new InvalidConfigException("Unrecognized level: $level");
                }
            }
        } else {
            $bitmapValues = array_reduce($levelMap, function ($carry, $item) {
                return $carry | $item;
            });
            if (!($bitmapValues & $levels) && $levels !== 0) {
                throw new InvalidConfigException("Incorrect $levels value");
            }
            $this->_levels = $levels;
        }
    }

    
    public static function filterMessages($messages, $levels = 0, $categories = [], $except = [])
    {
        foreach ($messages as $i => $message) {
            if ($levels && !($levels & $message[1])) {
                unset($messages[$i]);
                continue;
            }

            $matched = empty($categories);
            foreach ($categories as $category) {
                if ($message[2] === $category || !empty($category) && substr_compare($category, '*', -1, 1) === 0 && strpos($message[2], rtrim($category, '*')) === 0) {
                    $matched = true;
                    break;
                }
            }

            if ($matched) {
                foreach ($except as $category) {
                    $prefix = rtrim($category, '*');
                    if (($message[2] === $category || $prefix !== $category) && strpos($message[2], $prefix) === 0) {
                        $matched = false;
                        break;
                    }
                }
            }

            if (!$matched) {
                unset($messages[$i]);
            }
        }
        return $messages;
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
        $traces = [];
        if (isset($message[4])) {
            foreach ($message[4] as $trace) {
                $traces[] = "in {$trace['file']}:{$trace['line']}";
            }
        }

        $prefix = $this->getMessagePrefix($message);
        return date('Y-m-d H:i:s', $timestamp) . " {$prefix}[$level][$category] $text"
            . (empty($traces) ? '' : "\n    " . implode("\n    ", $traces));
    }

    
    public function getMessagePrefix($message)
    {
        if ($this->prefix !== null) {
            return call_user_func($this->prefix, $message);
        }

        if (Aabc::$app === null) {
            return '';
        }

        $request = Aabc::$app->getRequest();
        $ip = $request instanceof Request ? $request->getUserIP() : '-';

        /* @var $user \aabc\web\User */
        $user = Aabc::$app->has('user', true) ? Aabc::$app->get('user') : null;
        if ($user && ($identity = $user->getIdentity(false))) {
            $userID = $identity->getId();
        } else {
            $userID = '-';
        }

        /* @var $session \aabc\web\Session */
        $session = Aabc::$app->has('session', true) ? Aabc::$app->get('session') : null;
        $sessionID = $session && $session->getIsActive() ? $session->getId() : '-';

        return "[$ip][$userID][$sessionID]";
    }
}
