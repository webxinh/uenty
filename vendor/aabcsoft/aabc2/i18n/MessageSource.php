<?php


namespace aabc\i18n;

use Aabc;
use aabc\base\Component;


class MessageSource extends Component
{
    
    const EVENT_MISSING_TRANSLATION = 'missingTranslation';

    
    public $forceTranslation = false;
    
    public $sourceLanguage;

    private $_messages = [];


    
    public function init()
    {
        parent::init();
        if ($this->sourceLanguage === null) {
            $this->sourceLanguage = Aabc::$app->sourceLanguage;
        }
    }

    
    protected function loadMessages($category, $language)
    {
        return [];
    }

    
    public function translate($category, $message, $language)
    {
        if ($this->forceTranslation || $language !== $this->sourceLanguage) {
            return $this->translateMessage($category, $message, $language);
        } else {
            return false;
        }
    }

    
    protected function translateMessage($category, $message, $language)
    {
        $key = $language . '/' . $category;
        if (!isset($this->_messages[$key])) {
            $this->_messages[$key] = $this->loadMessages($category, $language);
        }
        if (isset($this->_messages[$key][$message]) && $this->_messages[$key][$message] !== '') {
            return $this->_messages[$key][$message];
        } elseif ($this->hasEventHandlers(self::EVENT_MISSING_TRANSLATION)) {
            $event = new MissingTranslationEvent([
                'category' => $category,
                'message' => $message,
                'language' => $language,
            ]);
            $this->trigger(self::EVENT_MISSING_TRANSLATION, $event);
            if ($event->translatedMessage !== null) {
                return $this->_messages[$key][$message] = $event->translatedMessage;
            }
        }

        return $this->_messages[$key][$message] = false;
    }
}
