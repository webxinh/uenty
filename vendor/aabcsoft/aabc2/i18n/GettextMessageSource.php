<?php


namespace aabc\i18n;

use Aabc;


class GettextMessageSource extends MessageSource
{
    const MO_FILE_EXT = '.mo';
    const PO_FILE_EXT = '.po';

    
    public $basePath = '@app/messages';
    
    public $catalog = 'messages';
    
    public $useMoFile = true;
    
    public $useBigEndian = false;


    
    protected function loadMessages($category, $language)
    {
        $messageFile = $this->getMessageFilePath($language);
        $messages = $this->loadMessagesFromFile($messageFile, $category);

        $fallbackLanguage = substr($language, 0, 2);
        $fallbackSourceLanguage = substr($this->sourceLanguage, 0, 2);

        if ($fallbackLanguage !== $language) {
            $messages = $this->loadFallbackMessages($category, $fallbackLanguage, $messages, $messageFile);
        } elseif ($language === $fallbackSourceLanguage) {
            $messages = $this->loadFallbackMessages($category, $this->sourceLanguage, $messages, $messageFile);
        } else {
            if ($messages === null) {
                Aabc::error("The message file for category '$category' does not exist: $messageFile", __METHOD__);
            }
        }

        return (array) $messages;
    }

    
    protected function loadFallbackMessages($category, $fallbackLanguage, $messages, $originalMessageFile)
    {
        $fallbackMessageFile = $this->getMessageFilePath($fallbackLanguage);
        $fallbackMessages = $this->loadMessagesFromFile($fallbackMessageFile, $category);

        if (
            $messages === null && $fallbackMessages === null
            && $fallbackLanguage !== $this->sourceLanguage
            && $fallbackLanguage !== substr($this->sourceLanguage, 0, 2)
        ) {
            Aabc::error("The message file for category '$category' does not exist: $originalMessageFile "
                . "Fallback file does not exist as well: $fallbackMessageFile", __METHOD__);
        } elseif (empty($messages)) {
            return $fallbackMessages;
        } elseif (!empty($fallbackMessages)) {
            foreach ($fallbackMessages as $key => $value) {
                if (!empty($value) && empty($messages[$key])) {
                    $messages[$key] = $fallbackMessages[$key];
                }
            }
        }

        return (array) $messages;
    }

    
    protected function getMessageFilePath($language)
    {
        $messageFile = Aabc::getAlias($this->basePath) . '/' . $language . '/' . $this->catalog;
        if ($this->useMoFile) {
            $messageFile .= self::MO_FILE_EXT;
        } else {
            $messageFile .= self::PO_FILE_EXT;
        }

        return $messageFile;
    }

    
    protected function loadMessagesFromFile($messageFile, $category)
    {
        if (is_file($messageFile)) {
            if ($this->useMoFile) {
                $gettextFile = new GettextMoFile(['useBigEndian' => $this->useBigEndian]);
            } else {
                $gettextFile = new GettextPoFile();
            }
            $messages = $gettextFile->load($messageFile, $category);
            if (!is_array($messages)) {
                $messages = [];
            }

            return $messages;
        } else {
            return null;
        }
    }
}
