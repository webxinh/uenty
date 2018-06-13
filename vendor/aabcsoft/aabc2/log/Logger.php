<?php


namespace aabc\log;

use Aabc;
use aabc\base\Component;


class Logger extends Component
{
    
    const LEVEL_ERROR = 0x01;
    
    const LEVEL_WARNING = 0x02;
    
    const LEVEL_INFO = 0x04;
    
    const LEVEL_TRACE = 0x08;
    
    const LEVEL_PROFILE = 0x40;
    
    const LEVEL_PROFILE_BEGIN = 0x50;
    
    const LEVEL_PROFILE_END = 0x60;

    
    public $messages = [];
    
    public $flushInterval = 1000;
    
    public $traceLevel = 0;
    
    public $dispatcher;


    
    public function init()
    {
        parent::init();
        register_shutdown_function(function () {
            // make regular flush before other shutdown functions, which allows session data collection and so on
            $this->flush();
            // make sure log entries written by shutdown functions are also flushed
            // ensure "flush()" is called last when there are multiple shutdown functions
            register_shutdown_function([$this, 'flush'], true);
        });
    }

    
    public function log($message, $level, $category = 'application')
    {
        $time = microtime(true);
        $traces = [];
        if ($this->traceLevel > 0) {
            $count = 0;
            $ts = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            array_pop($ts); // remove the last trace since it would be the entry script, not very useful
            foreach ($ts as $trace) {
                if (isset($trace['file'], $trace['line']) && strpos($trace['file'], AABC2_PATH) !== 0) {
                    unset($trace['object'], $trace['args']);
                    $traces[] = $trace;
                    if (++$count >= $this->traceLevel) {
                        break;
                    }
                }
            }
        }
        $this->messages[] = [$message, $level, $category, $time, $traces, memory_get_usage()];
        if ($this->flushInterval > 0 && count($this->messages) >= $this->flushInterval) {
            $this->flush();
        }
    }

    
    public function flush($final = false)
    {
        $messages = $this->messages;
        // https://github.com/aabcsoft/aabc2/issues/5619
        // new messages could be logged while the existing ones are being handled by targets
        $this->messages = [];
        if ($this->dispatcher instanceof Dispatcher) {
            $this->dispatcher->dispatch($messages, $final);
        }
    }

    
    public function getElapsedTime()
    {
        return microtime(true) - AABC_BEGIN_TIME;
    }

    
    public function getProfiling($categories = [], $excludeCategories = [])
    {
        $timings = $this->calculateTimings($this->messages);
        if (empty($categories) && empty($excludeCategories)) {
            return $timings;
        }

        foreach ($timings as $i => $timing) {
            $matched = empty($categories);
            foreach ($categories as $category) {
                $prefix = rtrim($category, '*');
                if (($timing['category'] === $category || $prefix !== $category) && strpos($timing['category'], $prefix) === 0) {
                    $matched = true;
                    break;
                }
            }

            if ($matched) {
                foreach ($excludeCategories as $category) {
                    $prefix = rtrim($category, '*');
                    foreach ($timings as $i => $timing) {
                        if (($timing['category'] === $category || $prefix !== $category) && strpos($timing['category'], $prefix) === 0) {
                            $matched = false;
                            break;
                        }
                    }
                }
            }

            if (!$matched) {
                unset($timings[$i]);
            }
        }

        return array_values($timings);
    }

    
    public function getDbProfiling()
    {
        $timings = $this->getProfiling(['aabc\db\Command::query', 'aabc\db\Command::execute']);
        $count = count($timings);
        $time = 0;
        foreach ($timings as $timing) {
            $time += $timing['duration'];
        }

        return [$count, $time];
    }

    
    public function calculateTimings($messages)
    {
        $timings = [];
        $stack = [];

        foreach ($messages as $i => $log) {
            list($token, $level, $category, $timestamp, $traces) = $log;
            $memory = isset($log[5]) ? $log[5] : 0;
            $log[6] = $i;
            if ($level == Logger::LEVEL_PROFILE_BEGIN) {
                $stack[] = $log;
            } elseif ($level == Logger::LEVEL_PROFILE_END) {
                if (($last = array_pop($stack)) !== null && $last[0] === $token) {
                    $timings[$last[6]] = [
                        'info' => $last[0],
                        'category' => $last[2],
                        'timestamp' => $last[3],
                        'trace' => $last[4],
                        'level' => count($stack),
                        'duration' => $timestamp - $last[3],
                        'memory' => $memory,
                        'memoryDiff' => $memory - (isset($last[5]) ? $last[5] : 0),
                    ];
                }
            }
        }

        ksort($timings);

        return array_values($timings);
    }


    
    public static function getLevelName($level)
    {
        static $levels = [
            self::LEVEL_ERROR => 'error',
            self::LEVEL_WARNING => 'warning',
            self::LEVEL_INFO => 'info',
            self::LEVEL_TRACE => 'trace',
            self::LEVEL_PROFILE_BEGIN => 'profile begin',
            self::LEVEL_PROFILE_END => 'profile end',
            self::LEVEL_PROFILE => 'profile'
        ];

        return isset($levels[$level]) ? $levels[$level] : 'unknown';
    }
}
