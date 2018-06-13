<?php


namespace aabc\i18n;

use Aabc;
use aabc\base\Component;
use aabc\base\InvalidConfigException;


class I18N extends Component
{
    
    public $translations;


    
    public function init()
    {
        parent::init();
        if (!isset($this->translations['aabc']) && !isset($this->translations['aabc*'])) {
            $this->translations['aabc'] = [
                'class' => 'aabc\i18n\PhpMessageSource',
                'sourceLanguage' => 'en-US',
                'basePath' => '@aabc/messages',
            ];
        }
        if (!isset($this->translations['app']) && !isset($this->translations['app*'])) {
            $this->translations['app'] = [
                'class' => 'aabc\i18n\PhpMessageSource',
                'sourceLanguage' => Aabc::$app->sourceLanguage,
                'basePath' => '@app/messages',
            ];
        }
    }

    
    public function translate($category, $message, $params, $language)
    {
        $messageSource = $this->getMessageSource($category);
        $translation = $messageSource->translate($category, $message, $language);
        if ($translation === false) {
            return $this->format($message, $params, $messageSource->sourceLanguage);
        } else {
            return $this->format($translation, $params, $language);
        }
    }

    
    public function format($message, $params, $language)
    {
        $params = (array) $params;
        if ($params === []) {
            return $message;
        }

        if (preg_match('~{\s*[\d\w]+\s*,~u', $message)) {
            $formatter = $this->getMessageFormatter();
            $result = $formatter->format($message, $params, $language);
            if ($result === false) {
                $errorMessage = $formatter->getErrorMessage();
                Aabc::warning("Formatting message for language '$language' failed with error: $errorMessage. The message being formatted was: $message.", __METHOD__);

                return $message;
            } else {
                return $result;
            }
        }

        $p = [];
        foreach ($params as $name => $value) {
            $p['{' . $name . '}'] = $value;
        }

        return strtr($message, $p);
    }

    
    private $_messageFormatter;

    
    public function getMessageFormatter()
    {
        if ($this->_messageFormatter === null) {
            $this->_messageFormatter = new MessageFormatter();
        } elseif (is_array($this->_messageFormatter) || is_string($this->_messageFormatter)) {
            $this->_messageFormatter = Aabc::createObject($this->_messageFormatter);
        }

        return $this->_messageFormatter;
    }

    
    public function setMessageFormatter($value)
    {
        $this->_messageFormatter = $value;
    }

    
    public function getMessageSource($category)
    {
        if (isset($this->translations[$category])) {
            $source = $this->translations[$category];
            if ($source instanceof MessageSource) {
                return $source;
            } else {
                return $this->translations[$category] = Aabc::createObject($source);
            }
        } else {
            // try wildcard matching
            foreach ($this->translations as $pattern => $source) {
                if (strpos($pattern, '*') > 0 && strpos($category, rtrim($pattern, '*')) === 0) {
                    if ($source instanceof MessageSource) {
                        return $source;
                    } else {
                        return $this->translations[$category] = $this->translations[$pattern] = Aabc::createObject($source);
                    }
                }
            }
            // match '*' in the last
            if (isset($this->translations['*'])) {
                $source = $this->translations['*'];
                if ($source instanceof MessageSource) {
                    return $source;
                } else {
                    return $this->translations[$category] = $this->translations['*'] = Aabc::createObject($source);
                }
            }
        }

        throw new InvalidConfigException("Unable to locate message source for category '$category'.");
    }
}
