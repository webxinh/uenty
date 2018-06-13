<?php


namespace aabc\base;

use Aabc;
use aabc\helpers\VarDumper;
use aabc\web\HttpException;


abstract class ErrorHandler extends Component
{
    
    public $discardExistingOutput = true;
    
    public $memoryReserveSize = 262144;
    
    public $exception;

    
    private $_memoryReserve;
    
    private $_hhvmException;


    
    public function register()
    {
        ini_set('display_errors', false);
        set_exception_handler([$this, 'handleException']);
        if (defined('HHVM_VERSION')) {
            set_error_handler([$this, 'handleHhvmError']);
        } else {
            set_error_handler([$this, 'handleError']);
        }
        if ($this->memoryReserveSize > 0) {
            $this->_memoryReserve = str_repeat('x', $this->memoryReserveSize);
        }
        register_shutdown_function([$this, 'handleFatalError']);
    }

    
    public function unregister()
    {
        restore_error_handler();
        restore_exception_handler();
    }

    
    public function handleException($exception)
    {
        if ($exception instanceof ExitException) {
            return;
        }

        $this->exception = $exception;

        // disable error capturing to avoid recursive errors while handling exceptions
        $this->unregister();

        // set preventive HTTP status code to 500 in case error handling somehow fails and headers are sent
        // HTTP exceptions will override this value in renderException()
        if (PHP_SAPI !== 'cli') {
            http_response_code(500);
        }

        try {
            $this->logException($exception);
            if ($this->discardExistingOutput) {
                $this->clearOutput();
            }
            $this->renderException($exception);
            if (!AABC_ENV_TEST) {
                \Aabc::getLogger()->flush(true);
                if (defined('HHVM_VERSION')) {
                    flush();
                }
                exit(1);
            }
        } catch (\Exception $e) {
            // an other exception could be thrown while displaying the exception
            $this->handleFallbackExceptionMessage($e, $exception);
        } catch (\Throwable $e) {
            // additional check for \Throwable introduced in PHP 7
            $this->handleFallbackExceptionMessage($e, $exception);
        }

        $this->exception = null;
    }

    
    protected function handleFallbackExceptionMessage($exception, $previousException) {
        $msg = "An Error occurred while handling another error:\n";
        $msg .= (string) $exception;
        $msg .= "\nPrevious exception:\n";
        $msg .= (string) $previousException;
        if (AABC_DEBUG) {
            if (PHP_SAPI === 'cli') {
                echo $msg . "\n";
            } else {
                echo '<pre>' . htmlspecialchars($msg, ENT_QUOTES, Aabc::$app->charset) . '</pre>';
            }
        } else {
            echo 'An internal server error occurred.';
        }
        $msg .= "\n\$_SERVER = " . VarDumper::export($_SERVER);
        error_log($msg);
        if (defined('HHVM_VERSION')) {
            flush();
        }
        exit(1);
    }

    
    public function handleHhvmError($code, $message, $file, $line, $context, $backtrace)
    {
        if ($this->handleError($code, $message, $file, $line)) {
            return true;
        }
        if (E_ERROR & $code) {
            $exception = new ErrorException($message, $code, $code, $file, $line);
            $ref = new \ReflectionProperty('\Exception', 'trace');
            $ref->setAccessible(true);
            $ref->setValue($exception, $backtrace);
            $this->_hhvmException = $exception;
        }
        return false;
    }

    
    public function handleError($code, $message, $file, $line)
    {
        if (error_reporting() & $code) {
            // load ErrorException manually here because autoloading them will not work
            // when error occurs while autoloading a class
            if (!class_exists('aabc\\base\\ErrorException', false)) {
                require_once(__DIR__ . '/ErrorException.php');
            }
            $exception = new ErrorException($message, $code, $code, $file, $line);

            // in case error appeared in __toString method we can't throw any exception
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            array_shift($trace);
            foreach ($trace as $frame) {
                if ($frame['function'] === '__toString') {
                    $this->handleException($exception);
                    if (defined('HHVM_VERSION')) {
                        flush();
                    }
                    exit(1);
                }
            }

            throw $exception;
        }
        return false;
    }

    
    public function handleFatalError()
    {
        unset($this->_memoryReserve);

        // load ErrorException manually here because autoloading them will not work
        // when error occurs while autoloading a class
        if (!class_exists('aabc\\base\\ErrorException', false)) {
            require_once(__DIR__ . '/ErrorException.php');
        }

        $error = error_get_last();

        if (ErrorException::isFatalError($error)) {
            if (!empty($this->_hhvmException)) {
                $exception = $this->_hhvmException;
            } else {
                $exception = new ErrorException($error['message'], $error['type'], $error['type'], $error['file'], $error['line']);
            }
            $this->exception = $exception;

            $this->logException($exception);

            if ($this->discardExistingOutput) {
                $this->clearOutput();
            }
            $this->renderException($exception);

            // need to explicitly flush logs because exit() next will terminate the app immediately
            Aabc::getLogger()->flush(true);
            if (defined('HHVM_VERSION')) {
                flush();
            }
            exit(1);
        }
    }

    
    abstract protected function renderException($exception);

    
    public function logException($exception)
    {
        $category = get_class($exception);
        if ($exception instanceof HttpException) {
            $category = 'aabc\\web\\HttpException:' . $exception->statusCode;
        } elseif ($exception instanceof \ErrorException) {
            $category .= ':' . $exception->getSeverity();
        }
        Aabc::error($exception, $category);
    }

    
    public function clearOutput()
    {
        // the following manual level counting is to deal with zlib.output_compression set to On
        for ($level = ob_get_level(); $level > 0; --$level) {
            if (!@ob_end_clean()) {
                ob_clean();
            }
        }
    }

    
    public static function convertExceptionToError($exception)
    {
        trigger_error(static::convertExceptionToString($exception), E_USER_ERROR);
    }

    
    public static function convertExceptionToString($exception)
    {
        if ($exception instanceof Exception && ($exception instanceof UserException || !AABC_DEBUG)) {
            $message = "{$exception->getName()}: {$exception->getMessage()}";
        } elseif (AABC_DEBUG) {
            if ($exception instanceof Exception) {
                $message = "Exception ({$exception->getName()})";
            } elseif ($exception instanceof ErrorException) {
                $message = "{$exception->getName()}";
            } else {
                $message = 'Exception';
            }
            $message .= " '" . get_class($exception) . "' with message '{$exception->getMessage()}' \n\nin "
                . $exception->getFile() . ':' . $exception->getLine() . "\n\n"
                . "Stack trace:\n" . $exception->getTraceAsString();
        } else {
            $message = 'Error: ' . $exception->getMessage();
        }
        return $message;
    }
}
