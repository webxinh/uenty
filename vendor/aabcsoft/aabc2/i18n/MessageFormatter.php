<?php


namespace aabc\i18n;

use Aabc;
use aabc\base\Component;
use aabc\base\NotSupportedException;


class MessageFormatter extends Component
{
    private $_errorCode = 0;
    private $_errorMessage = '';


    
    public function getErrorCode()
    {
        return $this->_errorCode;
    }

    
    public function getErrorMessage()
    {
        return $this->_errorMessage;
    }

    
    public function format($pattern, $params, $language)
    {
        $this->_errorCode = 0;
        $this->_errorMessage = '';

        if ($params === []) {
            return $pattern;
        }

        if (!class_exists('MessageFormatter', false)) {
            return $this->fallbackFormat($pattern, $params, $language);
        }

        // replace named arguments (https://github.com/aabcsoft/aabc2/issues/9678)
        $newParams = [];
        $pattern = $this->replaceNamedArguments($pattern, $params, $newParams);
        $params = $newParams;

        try {
            $formatter = new \MessageFormatter($language, $pattern);

            if ($formatter === null) {
                // formatter may be null in PHP 5.x
                $this->_errorCode = intl_get_error_code();
                $this->_errorMessage = 'Message pattern is invalid: ' . intl_get_error_message();
                return false;
            }
        } catch (\IntlException $e) {
            // IntlException is thrown since PHP 7
            $this->_errorCode = $e->getCode();
            $this->_errorMessage = 'Message pattern is invalid: ' . $e->getMessage();
            return false;
        } catch (\Exception $e) {
            // Exception is thrown by HHVM
            $this->_errorCode = $e->getCode();
            $this->_errorMessage = 'Message pattern is invalid: ' . $e->getMessage();
            return false;
        }

        $result = $formatter->format($params);

        if ($result === false) {
            $this->_errorCode = $formatter->getErrorCode();
            $this->_errorMessage = $formatter->getErrorMessage();
            return false;
        } else {
            return $result;
        }
    }

    
    public function parse($pattern, $message, $language)
    {
        $this->_errorCode = 0;
        $this->_errorMessage = '';

        if (!class_exists('MessageFormatter', false)) {
            throw new NotSupportedException('You have to install PHP intl extension to use this feature.');
        }

        // replace named arguments
        if (($tokens = self::tokenizePattern($pattern)) === false) {
            $this->_errorCode = -1;
            $this->_errorMessage = 'Message pattern is invalid.';

            return false;
        }
        $map = [];
        foreach ($tokens as $i => $token) {
            if (is_array($token)) {
                $param = trim($token[0]);
                if (!isset($map[$param])) {
                    $map[$param] = count($map);
                }
                $token[0] = $map[$param];
                $tokens[$i] = '{' . implode(',', $token) . '}';
            }
        }
        $pattern = implode('', $tokens);
        $map = array_flip($map);

        $formatter = new \MessageFormatter($language, $pattern);
        if ($formatter === null) {
            $this->_errorCode = -1;
            $this->_errorMessage = 'Message pattern is invalid.';

            return false;
        }
        $result = $formatter->parse($message);
        if ($result === false) {
            $this->_errorCode = $formatter->getErrorCode();
            $this->_errorMessage = $formatter->getErrorMessage();

            return false;
        } else {
            $values = [];
            foreach ($result as $key => $value) {
                $values[$map[$key]] = $value;
            }

            return $values;
        }
    }

    
    private function replaceNamedArguments($pattern, $givenParams, &$resultingParams = [], &$map = [])
    {
        if (($tokens = self::tokenizePattern($pattern)) === false) {
            return false;
        }
        foreach ($tokens as $i => $token) {
            if (!is_array($token)) {
                continue;
            }
            $param = trim($token[0]);
            if (isset($givenParams[$param])) {
                // if param is given, replace it with a number
                if (!isset($map[$param])) {
                    $map[$param] = count($map);
                    // make sure only used params are passed to format method
                    $resultingParams[$map[$param]] = $givenParams[$param];
                }
                $token[0] = $map[$param];
                $quote = '';
            } else {
                // quote unused token
                $quote = "'";
            }
            $type = isset($token[1]) ? trim($token[1]) : 'none';
            // replace plural and select format recursively
            if ($type === 'plural' || $type === 'select') {
                if (!isset($token[2])) {
                    return false;
                }
                if (($subtokens = self::tokenizePattern($token[2])) === false) {
                    return false;
                }
                $c = count($subtokens);
                for ($k = 0; $k + 1 < $c; $k++) {
                    if (is_array($subtokens[$k]) || !is_array($subtokens[++$k])) {
                        return false;
                    }
                    $subpattern = $this->replaceNamedArguments(implode(',', $subtokens[$k]), $givenParams, $resultingParams, $map);
                    $subtokens[$k] = $quote . '{' . $quote . $subpattern . $quote . '}' . $quote;
                }
                $token[2] = implode('', $subtokens);
            }
            $tokens[$i] = $quote . '{' . $quote . implode(',', $token) . $quote . '}' . $quote;
        }

        return implode('', $tokens);
    }

    
    protected function fallbackFormat($pattern, $args, $locale)
    {
        if (($tokens = self::tokenizePattern($pattern)) === false) {
            $this->_errorCode = -1;
            $this->_errorMessage = 'Message pattern is invalid.';

            return false;
        }
        foreach ($tokens as $i => $token) {
            if (is_array($token)) {
                if (($tokens[$i] = $this->parseToken($token, $args, $locale)) === false) {
                    $this->_errorCode = -1;
                    $this->_errorMessage = 'Message pattern is invalid.';

                    return false;
                }
            }
        }

        return implode('', $tokens);
    }

    
    private static function tokenizePattern($pattern)
    {
        $charset = Aabc::$app ? Aabc::$app->charset : 'UTF-8';
        $depth = 1;
        if (($start = $pos = mb_strpos($pattern, '{', 0, $charset)) === false) {
            return [$pattern];
        }
        $tokens = [mb_substr($pattern, 0, $pos, $charset)];
        while (true) {
            $open = mb_strpos($pattern, '{', $pos + 1, $charset);
            $close = mb_strpos($pattern, '}', $pos + 1, $charset);
            if ($open === false && $close === false) {
                break;
            }
            if ($open === false) {
                $open = mb_strlen($pattern, $charset);
            }
            if ($close > $open) {
                $depth++;
                $pos = $open;
            } else {
                $depth--;
                $pos = $close;
            }
            if ($depth === 0) {
                $tokens[] = explode(',', mb_substr($pattern, $start + 1, $pos - $start - 1, $charset), 3);
                $start = $pos + 1;
                $tokens[] = mb_substr($pattern, $start, $open - $start, $charset);
                $start = $open;
            }
        }
        if ($depth !== 0) {
            return false;
        }

        return $tokens;
    }

    
    private function parseToken($token, $args, $locale)
    {
        // parsing pattern based on ICU grammar:
        // http://icu-project.org/apiref/icu4c/classMessageFormat.html#details
        $charset = Aabc::$app ? Aabc::$app->charset : 'UTF-8';
        $param = trim($token[0]);
        if (isset($args[$param])) {
            $arg = $args[$param];
        } else {
            return '{' . implode(',', $token) . '}';
        }
        $type = isset($token[1]) ? trim($token[1]) : 'none';
        switch ($type) {
            case 'date':
            case 'time':
            case 'spellout':
            case 'ordinal':
            case 'duration':
            case 'choice':
            case 'selectordinal':
                throw new NotSupportedException("Message format '$type' is not supported. You have to install PHP intl extension to use this feature.");
            case 'number':
                $format = isset($token[2]) ? trim($token[2]) : null;
                if (is_numeric($arg) && ($format === null || $format === 'integer')) {
                    $number = number_format($arg);
                    if ($format === null && ($pos = strpos($arg, '.')) !== false) {
                        // add decimals with unknown length
                        $number .= '.' . substr($arg, $pos + 1);
                    }
                    return $number;
                }
                throw new NotSupportedException("Message format 'number' is only supported for integer values. You have to install PHP intl extension to use this feature.");
            case 'none':
                return $arg;
            case 'select':
                /* http://icu-project.org/apiref/icu4c/classicu_1_1SelectFormat.html
                selectStyle = (selector '{' message '}')+
                */
                if (!isset($token[2])) {
                    return false;
                }
                $select = self::tokenizePattern($token[2]);
                $c = count($select);
                $message = false;
                for ($i = 0; $i + 1 < $c; $i++) {
                    if (is_array($select[$i]) || !is_array($select[$i + 1])) {
                        return false;
                    }
                    $selector = trim($select[$i++]);
                    if ($message === false && $selector === 'other' || $selector == $arg) {
                        $message = implode(',', $select[$i]);
                    }
                }
                if ($message !== false) {
                    return $this->fallbackFormat($message, $args, $locale);
                }
                break;
            case 'plural':
                /* http://icu-project.org/apiref/icu4c/classicu_1_1PluralFormat.html
                pluralStyle = [offsetValue] (selector '{' message '}')+
                offsetValue = "offset:" number
                selector = explicitValue | keyword
                explicitValue = '=' number  // adjacent, no white space in between
                keyword = [^[[:Pattern_Syntax:][:Pattern_White_Space:]]]+
                message: see MessageFormat
                */
                if (!isset($token[2])) {
                    return false;
                }
                $plural = self::tokenizePattern($token[2]);
                $c = count($plural);
                $message = false;
                $offset = 0;
                for ($i = 0; $i + 1 < $c; $i++) {
                    if (is_array($plural[$i]) || !is_array($plural[$i + 1])) {
                        return false;
                    }
                    $selector = trim($plural[$i++]);

                    if ($i == 1 && strncmp($selector, 'offset:', 7) === 0) {
                        $offset = (int) trim(mb_substr($selector, 7, ($pos = mb_strpos(str_replace(["\n", "\r", "\t"], ' ', $selector), ' ', 7, $charset)) - 7, $charset));
                        $selector = trim(mb_substr($selector, $pos + 1, mb_strlen($selector, $charset), $charset));
                    }
                    if ($message === false && $selector === 'other' ||
                        $selector[0] === '=' && (int) mb_substr($selector, 1, mb_strlen($selector, $charset), $charset) === $arg ||
                        $selector === 'one' && $arg - $offset == 1
                    ) {
                        $message = implode(',', str_replace('#', $arg - $offset, $plural[$i]));
                    }
                }
                if ($message !== false) {
                    return $this->fallbackFormat($message, $args, $locale);
                }
                break;
        }

        return false;
    }
}
