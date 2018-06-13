<?php


namespace aabc\web;

use Aabc;
use aabc\base\Exception;
use aabc\base\ErrorException;
use aabc\base\UserException;
use aabc\helpers\VarDumper;


class ErrorHandler extends \aabc\base\ErrorHandler
{
    
    public $maxSourceLines = 19;
    
    public $maxTraceSourceLines = 13;
    
    public $errorAction;
    
    public $errorView = '@aabc/views/errorHandler/error.php';
    
    public $exceptionView = '@aabc/views/errorHandler/exception.php';
    
    public $callStackItemView = '@aabc/views/errorHandler/callStackItem.php';
    
    public $previousExceptionView = '@aabc/views/errorHandler/previousException.php';
    
    public $displayVars = ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION'];


    
    protected function renderException($exception)
    {
        if (Aabc::$app->has('response')) {
            $response = Aabc::$app->getResponse();
            // reset parameters of response to avoid interference with partially created response data
            // in case the error occurred while sending the response.
            $response->isSent = false;
            $response->stream = null;
            $response->data = null;
            $response->content = null;
        } else {
            $response = new Response();
        }

        $useErrorView = $response->format === Response::FORMAT_HTML && (!AABC_DEBUG || $exception instanceof UserException);

        if ($useErrorView && $this->errorAction !== null) {
            $result = Aabc::$app->runAction($this->errorAction);
            if ($result instanceof Response) {
                $response = $result;
            } else {
                $response->data = $result;
            }
        } elseif ($response->format === Response::FORMAT_HTML) {
            if (AABC_ENV_TEST || isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                // AJAX request
                $response->data = '<pre>' . $this->htmlEncode(static::convertExceptionToString($exception)) . '</pre>';
            } else {
                // if there is an error during error rendering it's useful to
                // display PHP error in debug mode instead of a blank screen
                if (AABC_DEBUG) {
                    ini_set('display_errors', 1);
                }
                $file = $useErrorView ? $this->errorView : $this->exceptionView;
                $response->data = $this->renderFile($file, [
                    'exception' => $exception,
                ]);
            }
        } elseif ($response->format === Response::FORMAT_RAW) {
            $response->data = static::convertExceptionToString($exception);
        } else {
            $response->data = $this->convertExceptionToArray($exception);
        }

        if ($exception instanceof HttpException) {
            $response->setStatusCode($exception->statusCode);
        } else {
            $response->setStatusCode(500);
        }

        $response->send();
    }

    
    protected function convertExceptionToArray($exception)
    {
        if (!AABC_DEBUG && !$exception instanceof UserException && !$exception instanceof HttpException) {
            $exception = new HttpException(500, Aabc::t('aabc', 'An internal server error occurred.'));
        }

        $array = [
            'name' => ($exception instanceof Exception || $exception instanceof ErrorException) ? $exception->getName() : 'Exception',
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
        ];
        if ($exception instanceof HttpException) {
            $array['status'] = $exception->statusCode;
        }
        if (AABC_DEBUG) {
            $array['type'] = get_class($exception);
            if (!$exception instanceof UserException) {
                $array['file'] = $exception->getFile();
                $array['line'] = $exception->getLine();
                $array['stack-trace'] = explode("\n", $exception->getTraceAsString());
                if ($exception instanceof \aabc\db\Exception) {
                    $array['error-info'] = $exception->errorInfo;
                }
            }
        }
        if (($prev = $exception->getPrevious()) !== null) {
            $array['previous'] = $this->convertExceptionToArray($prev);
        }

        return $array;
    }

    
    public function htmlEncode($text)
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    
    public function addTypeLinks($code)
    {
        if (preg_match('/(.*?)::([^(]+)/', $code, $matches)) {
            $class = $matches[1];
            $method = $matches[2];
            $text = $this->htmlEncode($class) . '::' . $this->htmlEncode($method);
        } else {
            $class = $code;
            $method = null;
            $text = $this->htmlEncode($class);
        }

        $url = null;

        $shouldGenerateLink = true;
        if ($method !== null) {
            $reflection = new \ReflectionMethod($class, $method);
            $shouldGenerateLink = $reflection->isPublic() || $reflection->isProtected();
        }

        if ($shouldGenerateLink) {
            $url = $this->getTypeUrl($class, $method);
        }

        if ($url === null) {
            return $text;
        }

        return '<a href="' . $url . '" target="_blank">' . $text . '</a>';
    }

    
    protected function getTypeUrl($class, $method)
    {
        if (strpos($class, 'aabc\\') !== 0) {
            return null;
        }

        $page = $this->htmlEncode(strtolower(str_replace('\\', '-', $class)));
        $url = "http://www.aabcframework.com/doc-2.0/$page.html";
        if ($method) {
            $url .= "#$method()-detail";
        }

        return $url;
    }

    
    public function renderFile($_file_, $_params_)
    {
        $_params_['handler'] = $this;
        if ($this->exception instanceof ErrorException || !Aabc::$app->has('view')) {
            ob_start();
            ob_implicit_flush(false);
            extract($_params_, EXTR_OVERWRITE);
            require(Aabc::getAlias($_file_));

            return ob_get_clean();
        } else {
            return Aabc::$app->getView()->renderFile($_file_, $_params_, $this);
        }
    }

    
    public function renderPreviousExceptions($exception)
    {
        if (($previous = $exception->getPrevious()) !== null) {
            return $this->renderFile($this->previousExceptionView, ['exception' => $previous]);
        } else {
            return '';
        }
    }

    
    public function renderCallStackItem($file, $line, $class, $method, $args, $index)
    {
        $lines = [];
        $begin = $end = 0;
        if ($file !== null && $line !== null) {
            $line--; // adjust line number from one-based to zero-based
            $lines = @file($file);
            if ($line < 0 || $lines === false || ($lineCount = count($lines)) < $line) {
                return '';
            }

            $half = (int) (($index === 1 ? $this->maxSourceLines : $this->maxTraceSourceLines) / 2);
            $begin = $line - $half > 0 ? $line - $half : 0;
            $end = $line + $half < $lineCount ? $line + $half : $lineCount - 1;
        }

        return $this->renderFile($this->callStackItemView, [
            'file' => $file,
            'line' => $line,
            'class' => $class,
            'method' => $method,
            'index' => $index,
            'lines' => $lines,
            'begin' => $begin,
            'end' => $end,
            'args' => $args,
        ]);
    }

    
    public function renderRequest()
    {
        $request = '';
        foreach ($this->displayVars as $name) {
            if (!empty($GLOBALS[$name])) {
                $request .= '$' . $name . ' = ' . VarDumper::export($GLOBALS[$name]) . ";\n\n";
            }
        }

        return '<pre>' . $this->htmlEncode(rtrim($request, "\n")) . '</pre>';
    }

    
    public function isCoreFile($file)
    {
        return $file === null || strpos(realpath($file), AABC2_PATH . DIRECTORY_SEPARATOR) === 0;
    }

    
    public function createHttpStatusLink($statusCode, $statusDescription)
    {
        return '<a href="http://en.wikipedia.org/wiki/List_of_HTTP_status_codes#' . (int) $statusCode . '" target="_blank">HTTP ' . (int) $statusCode . ' &ndash; ' . $statusDescription . '</a>';
    }

    
    public function createServerInformationLink()
    {
        $serverUrls = [
            'http://httpd.apache.org/' => ['apache'],
            'http://nginx.org/' => ['nginx'],
            'http://lighttpd.net/' => ['lighttpd'],
            'http://gwan.com/' => ['g-wan', 'gwan'],
            'http://iis.net/' => ['iis', 'services'],
            'http://php.net/manual/en/features.commandline.webserver.php' => ['development'],
        ];
        if (isset($_SERVER['SERVER_SOFTWARE'])) {
            foreach ($serverUrls as $url => $keywords) {
                foreach ($keywords as $keyword) {
                    if (stripos($_SERVER['SERVER_SOFTWARE'], $keyword) !== false) {
                        return '<a href="' . $url . '" target="_blank">' . $this->htmlEncode($_SERVER['SERVER_SOFTWARE']) . '</a>';
                    }
                }
            }
        }

        return '';
    }

    
    public function createFrameworkVersionLink()
    {
        return '<a href="http://github.com/aabcsoft/aabc2/" target="_blank">' . $this->htmlEncode(Aabc::getVersion()) . '</a>';
    }

    
    public function argumentsToString($args)
    {
        $count = 0;
        $isAssoc = $args !== array_values($args);

        foreach ($args as $key => $value) {
            $count++;
            if ($count>=5) {
                if ($count>5) {
                    unset($args[$key]);
                } else {
                    $args[$key] = '...';
                }
                continue;
            }

            if (is_object($value)) {
                $args[$key] = '<span class="title">' . $this->htmlEncode(get_class($value)) . '</span>';
            } elseif (is_bool($value)) {
                $args[$key] = '<span class="keyword">' . ($value ? 'true' : 'false') . '</span>';
            } elseif (is_string($value)) {
                $fullValue = $this->htmlEncode($value);
                if (mb_strlen($value, 'UTF-8') > 32) {
                    $displayValue = $this->htmlEncode(mb_substr($value, 0, 32, 'UTF-8')) . '...';
                    $args[$key] = "<span class=\"string\" title=\"$fullValue\">'$displayValue'</span>";
                } else {
                    $args[$key] = "<span class=\"string\">'$fullValue'</span>";
                }
            } elseif (is_array($value)) {
                $args[$key] = '[' . $this->argumentsToString($value) . ']';
            } elseif ($value === null) {
                $args[$key] = '<span class="keyword">null</span>';
            } elseif (is_resource($value)) {
                $args[$key] = '<span class="keyword">resource</span>';
            } else {
                $args[$key] = '<span class="number">' . $value . '</span>';
            }

            if (is_string($key)) {
                $args[$key] = '<span class="string">\'' . $this->htmlEncode($key) . "'</span> => $args[$key]";
            } elseif ($isAssoc) {
                $args[$key] = "<span class=\"number\">$key</span> => $args[$key]";
            }
        }

        return implode(', ', $args);
    }

    
    public function getExceptionName($exception)
    {
        if ($exception instanceof \aabc\base\Exception || $exception instanceof \aabc\base\InvalidCallException || $exception instanceof \aabc\base\InvalidParamException || $exception instanceof \aabc\base\UnknownMethodException) {
            return $exception->getName();
        }
        return null;
    }
}
