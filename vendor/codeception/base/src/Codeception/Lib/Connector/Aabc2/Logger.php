<?php
namespace Codeception\Lib\Connector\Aabc2;

use Codeception\Util\Debug;

class Logger extends \aabc\log\Logger
{
    public function init()
    {
        // overridden to prevent register_shutdown_function
    }

    public function log($message, $level, $category = 'application')
    {
        if (!in_array($level, [
            \aabc\log\Logger::LEVEL_INFO,
            \aabc\log\Logger::LEVEL_WARNING,
            \aabc\log\Logger::LEVEL_ERROR,
        ])) {
            return;
        }
        if (strpos($category, 'aabc\db\Command')===0) {
            return; // don't log queries
        }

        // https://github.com/Codeception/Codeception/issues/3696
        if ($message instanceof \aabc\base\Exception) {
            $message = $message->__toString();
        }

        Debug::debug("[$category] " .  \aabc\helpers\VarDumper::export($message));
    }
}
